<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint;

/**
 * Represents the details of a specific violation of a LogicalConstraint.
 */
final class LogicalConstraintViolation {

    public function __construct(
        public readonly string $message,
        public readonly LogicalConstraintViolationType $violationType
    ) {}

}
