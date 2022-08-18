<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\Exception\InvalidLogFileException;
use DateTime;
use DateTimeImmutable;
use Psr\Log\AbstractLogger;
use Stringable;

/**
 * @Internal
 */
final class FileLogger extends AbstractLogger {

    /**
     * @var callable():DateTimeImmutable
     */
    private $dateTimeProvider;

    /**
     * @param callable():DateTimeImmutable $dateTimeProvider
     */
    public function __construct(
        callable $dateTimeProvider,
        private readonly string $file
    ) {
        $this->dateTimeProvider = $dateTimeProvider;
        if (! @touch($this->file)) {
            throw new InvalidLogFileException(
                sprintf('Unable to write to log file "%s".', $this->file)
            );
        }
    }

    /**
     * @param mixed $level
     * @param Stringable|string $message
     * @param array<array-key, mixed> $context
     * @return void
     */
    public function log($level, Stringable|string $message, array $context = []) : void {
        $format = '[%s] annotated-container.%s: %s %s' . PHP_EOL;
        $contents = sprintf(
            $format,
            ($this->dateTimeProvider)()->format(DateTime::ATOM),
            strtoupper((string) $level),
            (string) $message,
            json_encode((object) $context)
        );

        file_put_contents($this->file, $contents, FILE_APPEND);
    }
}