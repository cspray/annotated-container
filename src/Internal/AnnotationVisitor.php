<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceProfile;
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

final class AnnotationVisitor extends NodeVisitorAbstract implements NodeVisitor {

    private AnnotationDetailsList $annotationDetails;

    public function __construct(private SplFileInfo $fileInfo) {
        $this->annotationDetails = new AnnotationDetailsList();
    }

    public function enterNode(Node $node) {
        if ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_) {
            $serviceAttribute = $this->findAttribute(Service::class, ...$node->attrGroups);
            if (isset($serviceAttribute)) {
                $annotationArguments = $this->getAnnotationArguments($serviceAttribute);
                $serviceProfile = $this->findAttribute(ServiceProfile::class, ...$node->attrGroups);
                if (!isset($serviceProfile)) {
                    $annotationArguments->put('profiles', ['default']);
                } else {
                    $annotationArguments->put('profiles', $this->getAttributeArgumentValue($serviceProfile->args[0]));
                }

                $this->annotationDetails->add(new AnnotationDetails(
                    $this->fileInfo,
                    AttributeType::Service,
                    $annotationArguments,
                    $this->getReflectionClass($node->namespacedName->toString())
                ));
            }
        } else if ($node instanceof Node\Stmt\ClassMethod) {
            foreach ([AttributeType::ServicePrepare, AttributeType::ServiceDelegate] as $attributeType) {
                $serviceMethodAttribute = $this->findAttribute($attributeType->value, ...$node->attrGroups);
                if (isset($serviceMethodAttribute))  {
                    $reflectionMethod = new ReflectionMethod(
                        $node->getAttribute('parent')->namespacedName->toString(),
                        $node->name->toString()
                    );
                    $this->annotationDetails->add(new AnnotationDetails($this->fileInfo, $attributeType, $this->getAnnotationArguments($serviceMethodAttribute), $reflectionMethod));
                }
            }
        } else if ($node instanceof Node\Param) {
            foreach ([AttributeType::InjectScalar, AttributeType::InjectEnv, AttributeType::InjectService] as $attributeType) {
                $injectAttribute = $this->findAttribute($attributeType->value, ...$node->attrGroups);
                if (isset($injectAttribute)) {
                    $methodNode = $node->getAttribute('parent');
                    $classNode = $methodNode->getAttribute('parent');
                    $reflectionParameter = new ReflectionParameter([$classNode->namespacedName->toString(), $methodNode->name->toString()], $node->var->name);
                    $annotationArguments = $this->getAnnotationArguments($injectAttribute);

                    $this->annotationDetails->add(new AnnotationDetails($this->fileInfo, $attributeType, $annotationArguments, $reflectionParameter));
                }
            }
        }
    }

    private function findAttribute(string $attributeType, AttributeGroup... $attributeGroups) : ?Attribute {
        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if ($attribute->name->toString() === $attributeType) {
                    return $attribute;
                }
            }
        }

        return null;
    }

    private function getAnnotationArguments(Attribute $attribute) : AnnotationArguments {
        $arguments = new AnnotationArguments();
        $ordinalArgumentNames = $this->getOrdinalArgumentNames($attribute);
        foreach ($attribute->args as $index => $arg) {
            $name = $arg->name ?? $ordinalArgumentNames[$index];
            $arguments->put($name, $this->getAttributeArgumentValue($arg));
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

    private function getAttributeArgumentValue(Node\Arg|Node\Expr\ArrayItem $arg) : string|bool|float|int|array {
        static $constEvaluator;
        if (!isset($constEvaluator)) {
            $constEvaluator = new ConstExprEvaluator(function(Node\Expr $expr) {
                if ($expr instanceof Node\Expr\ClassConstFetch) {
                    $type = $expr->class->toString();
                    $const = $expr->name->toString();
                    if ($const === 'class') {
                        // When you call Object::class the node becomes a ClassConstFetch with the class constant understood to be
                        // a "magic" constant that corresponds to the type that is being called on.
                        return $type;
                    } else {
                        // We are intentionally deferring the evaluation of the constant expression here until runtime when the
                        // Injector is instantiated because the constant being evaluated may not actually be loaded within the
                        // environment that is compiling the attributes. It is also possible that environment differences are
                        // encapsulated in the constant values which could also cause failures as unexpected values are encountered
                        // in different environments. If environment values are encapsulated in constants not deferring could also
                        // pose a potential security risk as those potentially sensitive values would be stored in plaintext in the
                        // serialization of the ContainerDefinition
                        return "!const(${type}::${const})";
                    }
                } else if ($expr instanceof Node\Expr\ConstFetch) {
                    $constName = $expr->name->getAttribute('namespacedName')->toString();
                    return "!const({$constName})";
                }

                throw new ConstExprEvaluationException("Expression of type {$expr->getType()} cannot be evaluated.");
            });
        }

        if (
            $arg->value instanceof Node\Scalar\String_ ||
            $arg->value instanceof Node\Scalar\LNumber ||
            $arg->value instanceof Node\Scalar\DNumber
        ) {
            $value = $arg->value->value;
        } else if ($arg->value instanceof Node\Expr\ConstFetch || $arg->value instanceof Node\Expr\ClassConstFetch) {
            $value = $constEvaluator->evaluateDirectly($arg->value);
        } else if ($arg->value instanceof Node\Expr\UnaryMinus) {
            $value = $arg->value->expr->value * -1;
        } else if ($arg->value instanceof Node\Expr\Array_) {
            $value = [];
            /** @var Node\Expr\ArrayItem $arrayItem */
            foreach ($arg->value->items as $arrayItem) {
                $value[] = $this->getAttributeArgumentValue($arrayItem);
            }
        } else {
            $value = null;
        }

        return $value;
    }

}