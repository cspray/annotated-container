<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Visitor;

use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\ServiceDefinition;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Class_;

final class ServiceDefinitionVisitor extends NodeVisitorAbstract implements NodeVisitor {

    private array $serviceDefinitions = [];

    public function enterNode(Node $node) {
        if ($node instanceof Class_ || $node instanceof Interface_) {
            $attributeGroups = $node->attrGroups;
            $serviceAttribute = $this->findServiceAttribute(...$attributeGroups);
            if (isset($serviceAttribute)) {
                $this->serviceDefinitions[] = [
                    'definitionType' => ServiceDefinition::class,
                    'type' => $node->namespacedName->toString(),
                    'environments' => $this->getServiceAttributeEnvironments($serviceAttribute),
                    'implements' => $this->getTypeImplements($node),
                    'isInterface' => $node instanceof Node\Stmt\Interface_
                ];
            }
        }
    }

    private function findServiceAttribute(AttributeGroup... $attributeGroups) : ?Attribute {
        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if ($attribute->name->toString() === Service::class) {
                    return $attribute;
                }
            }
        }

        return null;
    }

    private function getTypeImplements(Node $node) : array {
        $implements = [];
        if ($node instanceof Class_) {
            foreach ($node->implements as $interfaceName) {
                $implements[] = $interfaceName->toString();
            }
        }
        return $implements;
    }

    private function getServiceAttributeEnvironments(Attribute $attribute) : array {
        $environments = [];
        foreach ($attribute->args as $arg) {
            if ($arg->name->toString() === 'environments') {
                foreach ($arg->value->items as $argItem) {
                    $environments[] = $argItem->value->value;
                }
            }
        }

        return $environments;
    }

    public function getServiceDefinitions() : array {
        return $this->serviceDefinitions;
    }

}