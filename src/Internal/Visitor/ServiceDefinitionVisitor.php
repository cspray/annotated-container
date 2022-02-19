<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal\Visitor;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceProfile;
use Cspray\AnnotatedContainer\ServiceDefinition;
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
                    'profiles' => $this->getServiceAttributeEnvironments($node),
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

    private function getServiceAttributeEnvironments(Node $node) : array {
        $attribute = $this->findAttribute(ServiceProfile::class, ...$node->attrGroups);
        if ($attribute === null) {
            return [];
        }
        $profiles = [];
        $profiles[] = $attribute->args[0]->value->items[0]->value->value;
        return $profiles;
    }

    public function getServiceDefinitions() : array {
        return $this->serviceDefinitions;
    }

}