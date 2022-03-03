<?php

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ServiceDefinition;

/**
 * A LogicalConstraint that will check each abstract Service to ensure that at least 1 concrete Service is aliased for it.
 */
final class NoAbstractServiceAliasLogicalConstraint implements LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition): LogicalConstraintViolationCollection {
        $collection = new LogicalConstraintViolationCollection();
        foreach ($containerDefinition->getServiceDefinitions() as $sharedServiceDefinition) {
            if ($sharedServiceDefinition->isAbstract()) {
                if (!$this->doesServiceDefinitionHaveAlias($containerDefinition, $sharedServiceDefinition)) {
                    $collection->add(new LogicalConstraintViolation(sprintf(
                        'The abstract, %s, does not have an alias. Create a concrete class that implements this type and annotate it with a #[Service] Attribute.',
                        $sharedServiceDefinition->getType()
                    ), LogicalConstraintViolationType::Warning));
                }
            }
        }
        return $collection;
    }

    private function doesServiceDefinitionHaveAlias(ContainerDefinition $containerDefinition, ServiceDefinition $serviceDefinition) : bool {
        $hasAlias = false;
        foreach ($containerDefinition->getAliasDefinitions() as $aliasDefinition) {
            if ($aliasDefinition->getAbstractService()->getType() === $serviceDefinition->getType()) {
                $hasAlias = true;
                break;
            }
        }
        return $hasAlias;
    }

}