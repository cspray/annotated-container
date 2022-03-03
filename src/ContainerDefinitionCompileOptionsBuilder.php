<?php

namespace Cspray\AnnotatedContainer;

/**
 * The preferred method for constructing ContainerDefinitionCompileOptions
 */
final class ContainerDefinitionCompileOptionsBuilder {

    private array $directories = [];
    private array $profiles = [];

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

    /**
     * Specify the Profiles that are active when generating the ContainerDefinition.
     *
     * @param string ...$profiles
     * @return $this
     */
    public function withProfiles(string... $profiles) : self {
        $instance = clone $this;
        $instance->profiles = array_merge($this->profiles, $profiles);
        return $instance;
    }

    public function build() : ContainerDefinitionCompileOptions {
        return new class($this->directories, $this->profiles) implements ContainerDefinitionCompileOptions {

            private array $directories;
            private array $profiles;

            public function __construct(array $directories, array $profiles) {
                $this->directories = $directories;
                $this->profiles = $profiles;
            }

            public function getScanDirectories(): array {
                return $this->directories;
            }

            public function getProfiles(): array {
                return $this->profiles;
            }
        };
    }

}