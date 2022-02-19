<?php

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ServiceDefinition;
use Cspray\AnnotatedContainer\ServicePrepareDefinition;

class ServicePrepareRequiresServiceLogicalConstraint implements LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition): LogicalConstraintViolationCollection {
        $collection = new LogicalConstraintViolationCollection();
        foreach ($containerDefinition->getServicePrepareDefinitions() as $servicePrepareDefinition) {
            $serviceDefinition = $this->getServiceDefinition($containerDefinition, $servicePrepareDefinition);
            if (!isset($serviceDefinition)) {
                $collection->add(new LogicalConstraintViolation(
                    sprintf(
                        'The method %s::%s() is marked as a #[ServicePrepare] but the type is not a #[Service].',
                        $servicePrepareDefinition->getType(),
                        $servicePrepareDefinition->getMethod()
                    ),
                    LogicalConstraintViolationType::Warning
                ));
            }
        }
        return $collection;
    }

    private function getServiceDefinition(ContainerDefinition $containerDefinition, ServicePrepareDefinition $servicePrepareDefinition) : ?ServiceDefinition {
        foreach ($containerDefinition->getSharedServiceDefinitions() as $sharedServiceDefinition) {
            if ($sharedServiceDefinition->getType() === $servicePrepareDefinition->getType()) {
                return $sharedServiceDefinition;
            }
        }

        return null;
    }
}