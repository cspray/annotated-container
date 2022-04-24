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

    public function getContainerDefinitionBuilderContextConsumer() : ?ContainerDefinitionBuilderContextConsumer;

}