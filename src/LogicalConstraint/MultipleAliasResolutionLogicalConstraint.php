<?php

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ServiceDefinition;
use Cspray\AnnotatedContainer\DummyApps;

class MultipleAliasResolutionLogicalConstraint implements LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition): LogicalConstraintViolationCollection {
        $collection = new LogicalConstraintViolationCollection();
        foreach ($containerDefinition->getSharedServiceDefinitions() as $serviceDefinition) {
            $aliasCount = $this->getAliasCount($containerDefinition, $serviceDefinition);
            if ($aliasCount > 1) {
                $collection->add(new LogicalConstraintViolation(
                    sprintf('Multiple aliases were found for %s. This may be a fatal error at runtime.', DummyApps\MultipleAliasResolution\FooInterface::class),
                    LogicalConstraintViolationType::Notice
                ));
            }
        }
        return $collection;
    }

    private function getAliasCount(ContainerDefinition $containerDefinition, ServiceDefinition $serviceDefinition) : int {
        $count = 0;
        foreach ($containerDefinition->getAliasDefinitions() as $aliasDefinition) {
            if ($serviceDefinition->getType() === $aliasDefinition->getOriginalServiceDefinition()->getType()) {
                $count++;
            }
        }
        return $count;
    }
}