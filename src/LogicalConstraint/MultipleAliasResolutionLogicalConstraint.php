<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ServiceDefinition;
use Cspray\AnnotatedContainer\DummyApps;

/**
 * A LogicalConstraint that checks to see if any abstract ServiceDefinition has more than 1 concrete alias defined for it.
 *
 * The violation for this LogicalConstraint is a Notice. While it is _likely_ an error this might not be something we
 * wish to fail at during compile time. For a given service the end-developer might have annotated #[InjectService] in the
 * appropriate places to avoid alias resolution conflicts. We may reapproach this stance later.
 */
final class MultipleAliasResolutionLogicalConstraint implements LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition): LogicalConstraintViolationCollection {
        $collection = new LogicalConstraintViolationCollection();
        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
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
            if ($serviceDefinition->getType() === $aliasDefinition->getAbstractService()) {
                $count++;
            }
        }
        return $count;
    }
}