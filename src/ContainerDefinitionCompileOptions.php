<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

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
    public function getContainerDefinitionBuilderContextConsumer() : ?ContainerDefinitionBuilderContextConsumer;

}