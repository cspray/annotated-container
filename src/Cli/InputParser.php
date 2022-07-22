<?php

namespace Cspray\AnnotatedContainer\Cli;

use Cspray\AnnotatedContainer\Cli\Exception\OptionNotFound;

final class InputParser {

    public function parse(array $argv) : Input {
        array_shift($argv);
        $options = [];
        $arguments = [];

        $handleOption = function(string $arg) use(&$options) : void {
            if (str_contains($arg, '=')) {
                [$opt, $val] = explode('=', $arg);
            } else {
                $opt = $arg;
                $val = true;
            }
            $opt = str_replace('--', '', $opt);

            if (isset($options[$opt])) {
                $optVal = $val;
                if (!is_array($options[$opt])) {
                    $val = [$options[$opt]];
                } else {
                    $val = $options[$opt];
                }
                $val[] = $optVal;
            }
            $options[$opt] = $val;
        };

        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--')) {
                $handleOption($arg);
            } else if (str_starts_with($arg, '-')) {
                if (str_contains($arg, '=')) {
                    $handleOption('-' . $arg);
                } else {
                    $arg = str_replace('-', '', $arg);
                    $shortOpts = str_split($arg);
                    foreach ($shortOpts as $shortOpt) {
                        $handleOption('--' . $shortOpt);
                    }
                }
            } else {
                $arguments[] = $arg;
            }
        }

        return new class($options, $arguments) implements Input {

            public function __construct(
                private readonly array $options,
                private readonly array $args
            ) {}

            public function getOptions() : array {
                return $this->options;
            }

            public function getArguments() : array {
                return $this->args;
            }

            public function getOption(string $opt) : array|string|bool|null {
                return $this->options[$opt] ?? null;
            }

            public function requireOption(string $opt) : array|string|bool {
                if (!isset($this->options[$opt])) {
                    throw new OptionNotFound(sprintf('The option "%s" was not provided.', $opt));
                }

                return $this->options[$opt];
            }
        };
    }

}