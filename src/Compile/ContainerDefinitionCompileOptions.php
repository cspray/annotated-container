<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Compile;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointContainerDefinitionBuilderContextConsumer;
use Psr\Log\LoggerInterface;

/**
 * Represents configurable details for the compilation of a ContainerDefinition.
 */
interface ContainerDefinitionCompileOptions {

    /**
     * Return a list of directories to scan for annotated services.
     *
     * @return array
     */
    public function getScanDirectories() : array;

    /**
     * If you need to modify the ContainerDefinitionBuilder return a proper consumer, otherwise null.
     *
     * This is the primary entrypoint for adding third-party services that can't be annotated to the container.
     *
     * @return ContainerDefinitionBuilderContextConsumer|null
     */
    #[SingleEntrypointContainerDefinitionBuilderContextConsumer]
    public function getContainerDefinitionBuilderContextConsumer() : ?ContainerDefinitionBuilderContextConsumer;

    /**
     * If a LoggerInterface is returned information about the compilation and container creation process will be logged
     * to it.
     *
     * @return LoggerInterface|null
     */
    public function getLogger() : ?LoggerInterface;

}