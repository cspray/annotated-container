<?php

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ServiceDefinition;

final class NoAbstractServiceAliasLogicalConstraint implements LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition): LogicalConstraintViolationCollection {
        $collection = new LogicalConstraintViolationCollection();
        foreach ($containerDefinition->getServiceDefinitions() as $sharedServiceDefinition) {
            if ($sharedServiceDefinition->isInterface() || $sharedServiceDefinition->isAbstract()) {
                if (!$this->doesServiceDefinitionHaveAlias($containerDefinition, $sharedServiceDefinition)) {
                    $abstractType = $sharedServiceDefinition->isInterface() ? 'interface' : 'abstract class';
                    $abstractTypeImplementVerb = $sharedServiceDefinition->isInterface() ? 'implements' : 'extends';
                    $collection->add(new LogicalConstraintViolation(sprintf(
                        'The %s, %s, does not have an alias. Create a concrete class that %s this %s and annotate it with a #[Service] Attribute.',
                        $abstractType,
                        $sharedServiceDefinition->getType(),
                        $abstractTypeImplementVerb,
                        $abstractType
                    ), LogicalConstraintViolationType::Warning));
                }
            }
        }
        return $collection;
    }

    private function doesServiceDefinitionHaveAlias(ContainerDefinition $containerDefinition, ServiceDefinition $serviceDefinition) : bool {
        $hasAlias = false;
        foreach ($containerDefinition->getAliasDefinitions() as $aliasDefinition) {
            if ($aliasDefinition->getOriginalServiceDefinition()->getType() === $serviceDefinition->getType()) {
                $hasAlias = true;
                break;
            }
        }
        return $hasAlias;
    }

}