<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Internal\Visitor;

use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\ServiceDefinition;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Class_;

final class ServiceDefinitionVisitor extends AbstractNodeVisitor implements NodeVisitor {

    private array $serviceDefinitions = [];

    public function enterNode(Node $node) {
        if ($node instanceof Class_ || $node instanceof Interface_) {
            $serviceAttribute = $this->findAttribute(Service::class, ...$node->attrGroups);
            if (isset($serviceAttribute)) {
                $this->serviceDefinitions[] = [
                    'definitionType' => ServiceDefinition::class,
                    'type' => $node->namespacedName->toString(),
                    'environments' => $this->getServiceAttributeEnvironments($serviceAttribute),
                    'implements' => $this->getTypeImplements($node),
                    'extends' => $this->getTypeExtends($node),
                    'isInterface' => $node instanceof Interface_,
                    'isAbstract' => $node instanceof Class_ && $node->isAbstract()
                ];
            }
        }
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

    private function getTypeExtends(Node $node) : array {
        if ($node instanceof Class_ && isset($node->extends)) {
            return [$node->extends->toString()];
        }

        return [];
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