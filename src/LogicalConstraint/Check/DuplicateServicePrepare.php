<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraint;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationCollection;
use Cspray\AnnotatedContainer\Unit\LogicalConstraint\Check\LogicalConstraintTestCase;

final class DuplicateServicePrepare implements LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition, array $profiles) : LogicalConstraintViolationCollection {
        $violations = new LogicalConstraintViolationCollection();

        return $violations;
    }

}
