<?php

namespace Cspray\AnnotatedContainer;

/**
 * The preferred method for constructing ContainerDefinitionCompileOptions
 */
final class ContainerDefinitionCompileOptionsBuilder {

    private array $directories = [];

    private function __construct() {}

    /**
     * Specify the directories that should be parsed when generating the ContainerDefinition
     *
     * @param string ...$directories
     * @return static
     */
    public static function scanDirectories(string... $directories) : self {
        $instance = new self();
        $instance->directories = $directories;
        return $instance;
    }

    public function build() : ContainerDefinitionCompileOptions {
        return new class($this->directories) implements ContainerDefinitionCompileOptions {

            private array $directories;

            public function __construct(array $directories) {
                $this->directories = $directories;
            }

            public function getScanDirectories(): array {
                return $this->directories;
            }
        };
    }

}