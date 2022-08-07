<?php

namespace Cspray\AnnotatedContainer\Helper;

use Psr\Log\AbstractLogger;
use Stringable;

final class TestLogger extends AbstractLogger {

    /**
     * @var array<string, array{message: string, context: array<array-key, mixed>}>
     */
    private array $logs = [];

    public function log($level, Stringable|string $message, array $context = []) : void {
        assert(is_string($level));
        if (!isset($this->logs[$level])) {
            $this->logs[$level] = [];
        }

        $this->logs[$level][] = [
            'message' => (string) $message,
            'context' => $context
        ];
    }

    /**
     * @return array<string, array{message: string, context: array<array-key, mixed>}>
     */
    public function getLogs() : array {
        return $this->logs;
    }

    public function getLogsForLevel(string $level) : array {
        return $this->logs[$level] ?? [];
    }
}