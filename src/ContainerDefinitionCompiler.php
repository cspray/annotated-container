<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;

/**
 * An object that knows how to scan source code, analyze the annotations on it, and create a ContainerDefinition from
 * the resultant analysis.
 */
interface ContainerDefinitionCompiler {

    /**
     * Generate a ContainerDefinition from the source code and profiles defined by the $containerDefinitionCompileOptions.
     *
     * Throw an InvalidCompileOptionsException if some option passed is not valid or would result in an inability
     * to properly parse a ContainerDefinition.
     *
     * Throw an InvalidAnnotationException if some source code is annotated in such a way that a compilation error
     * occurs.
     *
     * @param ContainerDefinitionCompileOptions $containerDefinitionCompileOptions
     * @return ContainerDefinition
     * @throws InvalidCompileOptionsException
     * @throws InvalidAnnotationException
     */
    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions) : ContainerDefinition;

}