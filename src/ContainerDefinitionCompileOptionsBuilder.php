<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointContainerDefinitionBuilderContextConsumer;
use Psr\Log\LoggerInterface;

/**
 * The preferred method for constructing ContainerDefinitionCompileOptions
 */
final class ContainerDefinitionCompileOptionsBuilder {

    private array $directories = [];

    private ?ContainerDefinitionBuilderContextConsumer $consumer = null;

    private ?LoggerInterface $logger = null;

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
     * Specify that the ContainerDefinitionBuilder should be modified before the ContainerDefinition is built.
     *
     * @param ContainerDefinitionBuilderContextConsumer $consumer
     * @return $this
     */
    #[SingleEntrypointContainerDefinitionBuilderContextConsumer]
    public function withContainerDefinitionBuilderContextConsumer(ContainerDefinitionBuilderContextConsumer $consumer) : self {
        $instance = clone $this;
        $instance->consumer = $consumer;
        return $instance;
    }

    public function withLogger(LoggerInterface $logger) : self {
        $instance = clone $this;
        $instance->logger = $logger;
        return $instance;
    }

    public function build() : ContainerDefinitionCompileOptions {
        return new class(
            $this->directories,
            $this->consumer,
            $this->logger
        ) implements ContainerDefinitionCompileOptions {
            public function __construct(
                private readonly array $directories,
                private readonly ?ContainerDefinitionBuilderContextConsumer $consumer,
                private readonly ?LoggerInterface $logger
            ) {
            }

            public function getScanDirectories(): array {
                return $this->directories;
            }

            public function getContainerDefinitionBuilderContextConsumer(): ?ContainerDefinitionBuilderContextConsumer {
                return $this->consumer;
            }

            public function getLogger() : ?LoggerInterface {
                return $this->logger;
            }
        };
    }

}