<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Visitor;

use PhpParser\ConstExprEvaluationException;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

abstract class AbstractNodeVisitor extends NodeVisitorAbstract implements NodeVisitor {

    private ConstExprEvaluator $constEvaluator;

    public function __construct() {
        $this->constEvaluator = new ConstExprEvaluator(function(Node\Expr $expr) {
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
                    // serialization of the InjectorDefinition
                    return "!const(${type}::${const})";
                }
            } else if ($expr instanceof Node\Expr\ConstFetch) {
                $constName = $expr->name->getAttribute('namespacedName')->toString();
                return "!const({$constName})";
            }

            throw new ConstExprEvaluationException("Expression of type {$expr->getType()} cannot be evaluated.");
        });
    }

    protected function findAttribute(string $attributeType, AttributeGroup... $attributeGroups) : ?Attribute {
        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if ($attribute->name->toString() === $attributeType) {
                    return $attribute;
                }
            }
        }

        return null;
    }

    protected function getConstantEvaluator() : ConstExprEvaluator {
        return $this->constEvaluator;
    }

    protected function getAttributeArgumentValue(Node\Arg|Node\Expr\ArrayItem $arg) : string|bool|float|int|array {
        if (
            $arg->value instanceof Node\Scalar\String_ ||
            $arg->value instanceof Node\Scalar\LNumber ||
            $arg->value instanceof Node\Scalar\DNumber
        ) {
            $value = $arg->value->value;
        } else if ($arg->value instanceof Node\Expr\ConstFetch || $arg->value instanceof Node\Expr\ClassConstFetch) {
            $value = $this->getConstantEvaluator()->evaluateDirectly($arg->value);
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