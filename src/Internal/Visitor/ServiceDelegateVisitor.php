<?php

namespace Cspray\AnnotatedInjector\Internal\Visitor;

use Cspray\AnnotatedInjector\Attribute\ServiceDelegate;
use Cspray\AnnotatedInjector\ServiceDelegateDefinition;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

final class ServiceDelegateVisitor extends AbstractNodeVisitor {

    private array $serviceDelegateDefinitions = [];

    public function enterNode(Node $node) {
        if ($node instanceof ClassMethod) {
            $serviceDelegate = $this->findAttribute(ServiceDelegate::class, ...$node->attrGroups);
            if (isset($serviceDelegate)) {
                $this->serviceDelegateDefinitions[] = [
                    'definitionType' => ServiceDelegateDefinition::class,
                    'delegateType' => $node->getAttribute('parent')->namespacedName->toString(),
                    'delegateMethod' => $node->name->toString(),
                    'serviceType' => $this->getAttributeArgumentValue($serviceDelegate->args[0])
                ];
            }
        }
    }

    public function getServiceDelegateDefinitions() : array {
        return $this->serviceDelegateDefinitions;
    }

}