<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Visitor;

use Cspray\AnnotatedInjector\Attribute\DefineScalar;
use Cspray\AnnotatedInjector\Attribute\DefineScalarFromEnv;
use Cspray\AnnotatedInjector\DefineScalarDefinition;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\Node\Param;

final class DefineScalarDefinitionVisitor extends AbstractNodeVisitor implements NodeVisitor {

    private array $defineScalarDefinitions = [];

    public function enterNode(Node $node) {
        if ($node instanceof Param) {
            $defineScalarAttribute = $this->findAttribute(DefineScalar::class, ...$node->attrGroups);
            $defineScalarEnvAttribute = $this->findAttribute(DefineScalarFromEnv::class, ...$node->attrGroups);
            if (isset($defineScalarAttribute) || $defineScalarEnvAttribute) {
                // These calls are intentionally not chained together for future work that will do more checks on the
                // method and class that this attribute is defined on
                $methodNode = $node->getAttribute('parent');
                $classNode = $methodNode->getAttribute('parent');
                if (isset($defineScalarAttribute)) {
                    $valueArg = $defineScalarAttribute->args[0];
                } else {
                    $valueArg = $defineScalarEnvAttribute->args[0];
                }

                $value = $this->getAttributeArgumentValue($valueArg);
                if (isset($defineScalarEnvAttribute)) {
                    $value = "!env($value)";
                }

                $this->defineScalarDefinitions[] = [
                    'definitionType' => DefineScalarDefinition::class,
                    'type' => $classNode->namespacedName->toString(),
                    'method' => $methodNode->name->toString(),
                    'param' => $node->var->name,
                    'paramType' => $node->type->name,
                    'value' => $value,
                    'isPlainValue' => isset($defineScalarAttribute),
                    'isEnvironmentVar' => isset($defineScalarEnvAttribute)
                ];
            }
        }
    }


    public function getDefineScalarDefinitions() : array {
        return $this->defineScalarDefinitions;
    }

}