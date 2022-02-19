<?php

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\ContainerDefinition;

interface LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition) : LogicalConstraintViolationCollection;

}