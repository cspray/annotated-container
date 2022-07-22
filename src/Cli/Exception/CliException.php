<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Exception;

use Cspray\AnnotatedContainer\Exception\Exception;
use Throwable;

abstract class CliException extends Exception {

    final protected function __construct(
        string $message,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

}