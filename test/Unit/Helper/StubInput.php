<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\Cli\Exception\OptionNotFound;
use Cspray\AnnotatedContainer\Cli\Input;

final class StubInput implements Input {

    public function __construct(
        private readonly array $options,
        private readonly array $arguments
    ) {}

    public function getOptions() : array {
        return $this->options;
    }

    public function getArguments() : array {
        return $this->arguments;
    }

    public function getOption(string $opt) : array|string|bool|null {
        return $this->options[$opt] ?? null;
    }

    public function requireOption(string $opt) : array|string|bool {
        if (!isset($this->options[$opt])) {
            throw new OptionNotFound();
        }

        return $this->options[$opt];
    }
}