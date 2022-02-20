<?php

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

final class LogicalConstraintViolationCollection implements Countable, IteratorAggregate {

    private array $constraintViolations = [];

    public function addAll(LogicalConstraintViolationCollection $constraintViolationCollection) : void {
        foreach ($constraintViolationCollection as $constraintViolation) {
            $this->constraintViolations[] = $constraintViolation;
        }
    }

    public function add(LogicalConstraintViolation $logicalConstraintViolation) : void {
        $this->constraintViolations[] = $logicalConstraintViolation;
    }

    public function get(int $index) : ?LogicalConstraintViolation {
        return $this->constraintViolations[$index] ?? null;
    }

    public function getIterator() : Traversable {
        return new ArrayIterator($this->constraintViolations);
    }

    public function count() : int {
        return count($this->constraintViolations);
    }
}