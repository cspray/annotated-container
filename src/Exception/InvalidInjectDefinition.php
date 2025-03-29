<?php

namespace Cspray\AnnotatedContainer\Exception;

use Throwable;

final class InvalidInjectDefinition extends Exception {
    public static function fromValueNotSerializable(Throwable $throwable) : self {
        $message = 'An InjectDefinition with a value that cannot be serialized was provided.';
        return new self($message, previous: $throwable);
    }
}
