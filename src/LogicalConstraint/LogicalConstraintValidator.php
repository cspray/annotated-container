<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

/**
 * A class that ensures a ContainerDefinition adheres to the LogicalConstraints we have defined.
 */
final class LogicalConstraintValidator {

    /** @var LogicalConstraint[] */
    private array $logicalConstraints = [];

    public function __construct() {
        $this->logicalConstraints[] = new NoAbstractServiceAliasLogicalConstraint();
    }

    /**
     * Run all the LogicalConstraint implementations that this library defines and return a merged collection of any
     * violations that might exist in $containerDefinition.
     *
     * @param ContainerDefinition $containerDefinition
     * @return LogicalConstraintViolationCollection
     */
    public function validate(ContainerDefinition $containerDefinition) : LogicalConstraintViolationCollection {
        $collection = new LogicalConstraintViolationCollection();

        foreach ($this->logicalConstraints as $logicalConstraint) {
            $collection->addAll($logicalConstraint->getConstraintViolations($containerDefinition));
        }

        return $collection;
    }



}