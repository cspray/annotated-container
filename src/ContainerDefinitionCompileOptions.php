<?php

namespace Cspray\AnnotatedContainer;

/**
 * Represents configurable details for the compilation of a ContainerDefinition.
 */
interface ContainerDefinitionCompileOptions {

    /**
     * Return a list of directories to scan for annotated services.
     *
     * @TODO Rename this getScanTargets() to better support a ContainerDefinitionCompiler that does not parse the file directly but uses Reflection on a class
     * @return array
     */
    public function getScanDirectories() : array;

}