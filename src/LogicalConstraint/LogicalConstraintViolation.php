<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint;

/**
 * Represents the details of a specific violation of a LogicalConstraint.
 */
final class LogicalConstraintViolation {

    private string $message;
    private LogicalConstraintViolationType $violationType;

    public function __construct(string $message, LogicalConstraintViolationType $violationType) {
        $this->message = $message;
        $this->violationType = $violationType;
    }

    public function getMessage() : string {
        return $this->message;
    }

    public function getViolationType() : LogicalConstraintViolationType {
        return $this->violationType;
    }

}