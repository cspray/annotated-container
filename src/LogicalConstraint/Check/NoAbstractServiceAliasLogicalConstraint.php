<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraint;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolation;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationCollection;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;

/**
 * A LogicalConstraint that will check each abstract Service to ensure that at least 1 concrete Service is aliased for it.
 */
final class NoAbstractServiceAliasLogicalConstraint implements LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition, array $profiles): LogicalConstraintViolationCollection {
        $collection = new LogicalConstraintViolationCollection();
        foreach ($containerDefinition->getServiceDefinitions() as $sharedServiceDefinition) {
            if ($sharedServiceDefinition->isAbstract()) {
                if (!$this->doesServiceDefinitionHaveAlias($containerDefinition, $sharedServiceDefinition)) {
                    $collection->add(new LogicalConstraintViolation(sprintf(
                        'The abstract, %s, does not have an alias. Create a concrete class that implements this type and annotate it with a #[Service] Attribute.',
                        $sharedServiceDefinition->getType()->getName()
                    ), LogicalConstraintViolationType::Warning));
                }
            }
        }
        return $collection;
    }

    private function doesServiceDefinitionHaveAlias(ContainerDefinition $containerDefinition, ServiceDefinition $serviceDefinition) : bool {
        $hasAlias = false;
        foreach ($containerDefinition->getAliasDefinitions() as $aliasDefinition) {
            if ($aliasDefinition->getAbstractService() === $serviceDefinition->getType()) {
                $hasAlias = true;
                break;
            }
        }
        return $hasAlias;
    }

}