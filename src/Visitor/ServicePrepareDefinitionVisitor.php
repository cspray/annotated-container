<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Visitor;

use Cspray\AnnotatedInjector\Attribute\ServicePrepare;
use Cspray\AnnotatedInjector\ServicePrepareDefinition;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\ClassMethod;

final class ServicePrepareDefinitionVisitor extends NodeVisitorAbstract implements NodeVisitor {

    private array $servicePrepareDefinitions = [];

    public function enterNode(Node $node) {
        if ($node instanceof ClassMethod) {
            $attributeGroups = $node->attrGroups;
            foreach ($attributeGroups as $attributeGroup) {
                foreach ($attributeGroup->attrs as $attribute) {
                    if ($attribute->name->toString() === ServicePrepare::class) {
                        $this->servicePrepareDefinitions[] = [
                            'definitionType' => ServicePrepareDefinition::class,
                            'type' => $node->getAttribute('parent')->namespacedName->toString(),
                            'method' => $node->name->toString()
                        ];
                    }
                }
            }
        }
    }

    public function getServicePrepareDefinitions() : array {
        return $this->servicePrepareDefinitions;
    }

}