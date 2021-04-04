<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Visitor;

use Cspray\AnnotatedInjector\Attribute\ServiceSetup;
use Cspray\AnnotatedInjector\ServiceSetupDefinition;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\ClassMethod;

final class ServiceSetupDefinitionVisitor extends NodeVisitorAbstract implements NodeVisitor {

    private array $serviceSetupDefinitions = [];

    public function enterNode(Node $node) {
        if ($node instanceof ClassMethod) {
            $attributeGroups = $node->attrGroups;
            foreach ($attributeGroups as $attributeGroup) {
                foreach ($attributeGroup->attrs as $attribute) {
                    if ($attribute->name->toString() === ServiceSetup::class) {
                        $this->serviceSetupDefinitions[] = [
                            'definitionType' => ServiceSetupDefinition::class,
                            'type' => $node->getAttribute('parent')->namespacedName->toString(),
                            'method' => $node->name->toString()
                        ];
                    }
                }
            }
        }
    }

    public function getServiceSetupDefinitions() : array {
        return $this->serviceSetupDefinitions;
    }

}