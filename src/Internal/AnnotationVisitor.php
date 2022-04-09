<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\AnnotationValue;
use Cspray\AnnotatedContainer\Attribute\Service;
use PhpParser\ConstExprEvaluationException;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use SplFileInfo;
use function Cspray\AnnotatedContainer\arrayValue;
use function Cspray\AnnotatedContainer\constantValue;
use function Cspray\AnnotatedContainer\envValue;
use function Cspray\AnnotatedContainer\scalarValue;

/**
 * @Internal
 */
final class AnnotationVisitor extends NodeVisitorAbstract implements NodeVisitor {

    private AnnotationDetailsList $annotationDetails;

    public function __construct(private SplFileInfo $fileInfo) {
        $this->annotationDetails = new AnnotationDetailsList();
    }

    public function enterNode(Node $node) {
        if ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_) {
            $serviceAttributes = $this->findAttributes(Service::class, ...$node->attrGroups);
            if (!empty($serviceAttributes)) {
                $serviceAttribute = $serviceAttributes[0];
                $annotationArguments = $this->getAnnotationArguments(AttributeType::Service, $serviceAttribute);
                $this->annotationDetails->add(new AnnotationDetails(
                    $this->fileInfo,
                    AttributeType::Service,
                    $annotationArguments,
                    $this->getReflectionClass($node->namespacedName->toString())
                ));
            }
        } else if ($node instanceof Node\Stmt\ClassMethod) {
            foreach ([AttributeType::ServicePrepare, AttributeType::ServiceDelegate] as $attributeType) {
                $serviceMethodAttributes = $this->findAttributes($attributeType->value, ...$node->attrGroups);
                if (!empty($serviceMethodAttributes))  {
                    $serviceMethodAttribute = $serviceMethodAttributes[0];
                    $reflectionMethod = new ReflectionMethod(
                        $node->getAttribute('parent')->namespacedName->toString(),
                        $node->name->toString()
                    );
                    $this->annotationDetails->add(new AnnotationDetails(
                        $this->fileInfo,
                        $attributeType,
                        $this->getAnnotationArguments($attributeType, $serviceMethodAttribute),
                        $reflectionMethod
                    ));
                }
            }
        } else if ($node instanceof Node\Param) {
            foreach ([AttributeType::InjectScalar, AttributeType::InjectEnv, AttributeType::InjectService] as $attributeType) {
                $injectAttributes = $this->findAttributes($attributeType->value, ...$node->attrGroups);
                foreach ($injectAttributes as $injectAttribute) {
                    $methodNode = $node->getAttribute('parent');
                    $classNode = $methodNode->getAttribute('parent');
                    $reflectionParameter = new ReflectionParameter([$classNode->namespacedName->toString(), $methodNode->name->toString()], $node->var->name);
                    $annotationArguments = $this->getAnnotationArguments($attributeType, $injectAttribute);

                    $this->annotationDetails->add(new AnnotationDetails(
                        $this->fileInfo,
                        $attributeType,
                        $annotationArguments,
                        $reflectionParameter
                    ));
                }
            }
        }
    }

    /**
     * @param string $attributeType
     * @param AttributeGroup ...$attributeGroups
     * @return Attribute[]
     */
    private function findAttributes(string $attributeType, AttributeGroup... $attributeGroups) : array {
        $attributes = [];
        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if ($attribute->name->toString() === $attributeType) {
                    $attributes[] = $attribute;
                }
            }
        }

        return $attributes;
    }

    private function getAnnotationArguments(AttributeType $attributeType, Attribute $attribute) : AnnotationArguments {
        $arguments = new AnnotationArguments();
        $ordinalArgumentNames = $this->getOrdinalArgumentNames($attribute);
        foreach ($attribute->args as $index => $arg) {
            $name = $arg->name ?? $ordinalArgumentNames[$index];
            $arguments->put($name, $this->getAttributeArgumentValue($attributeType, $attribute, $arg));
        }

        return $arguments;
    }

    private function getOrdinalArgumentNames(Attribute $attribute) : array {
        $attributeConstructor = $this->getReflectionClass($attribute->name->toString())->getConstructor();
        if (is_null($attributeConstructor)) {
            return [];
        }
        $parameters = $attributeConstructor->getParameters();
        return array_map(fn(ReflectionParameter $param) => $param->name, $parameters);
    }

    private function getReflectionClass(string $class) : ReflectionClass {
        static $cache = [];
        if (!isset($cache[$class])) {
            $cache[$class] = new ReflectionClass($class);
        }

        return $cache[$class];
    }

    public function getAnnotationDetailsList() : AnnotationDetailsList {
        return $this->annotationDetails;
    }

    private function getAttributeArgumentValue(AttributeType $attributeType, Attribute $attribute, Node\Arg|Node\Expr\ArrayItem $arg) : ?AnnotationValue {
        static $constEvaluator;
        if (!isset($constEvaluator)) {
            $constEvaluator = new ConstExprEvaluator(function(Node\Expr $expr) {
                if ($expr instanceof Node\Expr\ClassConstFetch) {
                    $type = $expr->class->toString();
                    $const = $expr->name->toString();
                    if ($const === 'class') {
                        // When you call Object::class the node becomes a ClassConstFetch with the class constant understood to be
                        // a "magic" constant that corresponds to the type that is being called on.
                        return scalarValue($type);
                    } else {
                        // We are intentionally deferring the evaluation of the constant expression here until runtime when the
                        // Injector is instantiated because the constant being evaluated may not actually be loaded within the
                        // environment that is compiling the attributes. It is also possible that environment differences are
                        // encapsulated in the constant values which could also cause failures as unexpected values are encountered
                        // in different environments. If environment values are encapsulated in constants not deferring could also
                        // pose a potential security risk as those potentially sensitive values would be stored in plaintext in the
                        // serialization of the ContainerDefinition
                        return constantValue("$type::$const");
                    }
                } else if ($expr instanceof Node\Expr\ConstFetch) {
                    $constName = $expr->name->getAttribute('namespacedName')->toString();
                    return constantValue($constName);
                }

                throw new ConstExprEvaluationException("Expression of type {$expr->getType()} cannot be evaluated.");
            });
        }

        // If the value doesn't have a name then the $arg node must equal the first argument
        if ($attributeType === AttributeType::InjectEnv && ((!isset($arg->name) && $arg === $attribute->args[0]) || (isset($arg->name) && $arg->name === 'value'))) {
            return envValue($arg->value->value);
        }

        if (
            $arg->value instanceof Node\Scalar\String_ ||
            $arg->value instanceof Node\Scalar\LNumber ||
            $arg->value instanceof Node\Scalar\DNumber
        ) {
            $value = scalarValue($arg->value->value);
        } else if ($arg->value instanceof Node\Expr\ConstFetch || $arg->value instanceof Node\Expr\ClassConstFetch) {
            $value = $constEvaluator->evaluateDirectly($arg->value);
            if (!$value instanceof AnnotationValue) {
                $value = scalarValue($value);
            }
        } else if ($arg->value instanceof Node\Expr\UnaryMinus) {
            $value = scalarValue(-($arg->value->expr->value));
        } else if ($arg->value instanceof Node\Expr\Array_) {
            $value = [];
            /** @var Node\Expr\ArrayItem $arrayItem */
            foreach ($arg->value->items as $arrayItem) {
                $value[] = $this->getAttributeArgumentValue($attributeType, $attribute, $arrayItem);
            }
            $value = arrayValue($value);
        } else {
            $value = null;
        }

        return $value;
    }

}