<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * The preferred method for constructing ContainerDefinitionCompileOptions
 */
final class ContainerDefinitionCompileOptionsBuilder {

    private array $directories = [];

    private ?ContainerDefinitionBuilderContextConsumer $consumer = null;

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

    public function withContainerDefinitionBuilderContextConsumer(ContainerDefinitionBuilderContextConsumer $consumer) : self {
        $instance = clone $this;
        $instance->consumer = $consumer;
        return $instance;
    }

    public function build() : ContainerDefinitionCompileOptions {
        return new class($this->directories, $this->consumer) implements ContainerDefinitionCompileOptions {
            public function __construct(private array $directories, private ?ContainerDefinitionBuilderContextConsumer $consumer) {
            }

            public function getScanDirectories(): array {
                return $this->directories;
            }

            public function getContainerDefinitionBuilderContextConsumer(): ?ContainerDefinitionBuilderContextConsumer {
                return $this->consumer;
            }
        };
    }

}