<?php

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\ContainerDefinition;

final class LogicalConstraintValidator {

    /** @var LogicalConstraint[] */
    private array $logicalConstraints = [];

    public function __construct() {
        $this->logicalConstraints[] = new MultipleAliasResolutionLogicalConstraint();
        $this->logicalConstraints[] = new NoAbstractServiceAliasLogicalConstraint();
    }

    public function validate(ContainerDefinition $containerDefinition) : LogicalConstraintViolationCollection {
        $collection = new LogicalConstraintViolationCollection();

        foreach ($this->logicalConstraints as $logicalConstraint) {
            $collection->addAll($logicalConstraint->getConstraintViolations($containerDefinition));
        }

        return $collection;
    }



}