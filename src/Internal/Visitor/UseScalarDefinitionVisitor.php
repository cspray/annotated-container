<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Internal\Visitor;

use Cspray\AnnotatedInjector\Attribute\UseScalar;
use Cspray\AnnotatedInjector\Attribute\UseScalarFromEnv;
use Cspray\AnnotatedInjector\UseScalarDefinition;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\Node\Param;

final class UseScalarDefinitionVisitor extends AbstractNodeVisitor implements NodeVisitor {

    private array $UseScalarDefinitions = [];

    public function enterNode(Node $node) {
        if ($node instanceof Param) {
            $useScalarAttribute = $this->findAttribute(UseScalar::class, ...$node->attrGroups);
            $useScalarEnvAttribute = $this->findAttribute(UseScalarFromEnv::class, ...$node->attrGroups);
            if (isset($useScalarAttribute) || $useScalarEnvAttribute) {
                // These calls are intentionally not chained together for future work that will do more checks on the
                // method and class that this attribute is defined on
                $methodNode = $node->getAttribute('parent');
                $classNode = $methodNode->getAttribute('parent');
                if (isset($useScalarAttribute)) {
                    $valueArg = $useScalarAttribute->args[0];
                } else {
                    $valueArg = $useScalarEnvAttribute->args[0];
                }

                $value = $this->getAttributeArgumentValue($valueArg);
                if (isset($useScalarEnvAttribute)) {
                    $value = "!env($value)";
                }

                $this->UseScalarDefinitions[] = [
                    'definitionType' => UseScalarDefinition::class,
                    'type' => $classNode->namespacedName->toString(),
                    'method' => $methodNode->name->toString(),
                    'param' => $node->var->name,
                    'paramType' => $node->type->name,
                    'value' => $value
                ];
            }
        }
    }


    public function getUseScalarDefinitions() : array {
        return $this->UseScalarDefinitions;
    }

}