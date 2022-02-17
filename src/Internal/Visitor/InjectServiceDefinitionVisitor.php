<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal\Visitor;

use Cspray\AnnotatedContainer\Attribute\InjectService;
use Cspray\AnnotatedContainer\InjectServiceDefinition;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\NodeVisitor;

final class InjectServiceDefinitionVisitor extends AbstractNodeVisitor implements NodeVisitor {

    private array $UseServiceDefinitions = [];

    public function enterNode(Node $node) {
        if ($node instanceof Param) {
            $UseServiceAttribute = $this->findAttribute(InjectService::class, ...$node->attrGroups);
            if (isset($UseServiceAttribute)) {
                $methodNode = $node->getAttribute('parent');
                $classNode = $methodNode->getAttribute('parent');
                $valueArg = $UseServiceAttribute->args[0];

                $this->UseServiceDefinitions[] = [
                    'definitionType' => InjectServiceDefinition::class,
                    'type' => $classNode->namespacedName->toString(),
                    'method' => $methodNode->name->toString(),
                    'param' => $node->var->name,
                    'paramType' => $node->type->toString(),
                    'value' => $this->getAttributeArgumentValue($valueArg)
                ];
            }
        }
    }

    public function getUseServiceDefinitions() : array {
        return $this->UseServiceDefinitions;
    }

}