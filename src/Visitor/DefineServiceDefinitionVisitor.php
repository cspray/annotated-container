<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Visitor;

use Cspray\AnnotatedInjector\Attribute\DefineService;
use Cspray\AnnotatedInjector\DefineServiceDefinition;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\NodeVisitor;

final class DefineServiceDefinitionVisitor extends AbstractNodeVisitor implements NodeVisitor {

    private array $defineServiceDefinitions = [];

    public function enterNode(Node $node) {
        if ($node instanceof Param) {
            $defineServiceAttribute = $this->findAttribute(DefineService::class, ...$node->attrGroups);
            if (isset($defineServiceAttribute)) {
                $methodNode = $node->getAttribute('parent');
                $classNode = $methodNode->getAttribute('parent');
                $valueArg = $defineServiceAttribute->args[0];

                $this->defineServiceDefinitions[] = [
                    'definitionType' => DefineServiceDefinition::class,
                    'type' => $classNode->namespacedName->toString(),
                    'method' => $methodNode->name->toString(),
                    'param' => $node->var->name,
                    'paramType' => $node->type->toString(),
                    'value' => $this->getAttributeArgumentValue($valueArg)
                ];
            }
        }
    }

    public function getDefineServiceDefinitions() : array {
        return $this->defineServiceDefinitions;
    }

}