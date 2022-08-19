<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Compile;

use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptions;
use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;

/**
 * An object that knows how to create a ContainerDefinition instance from a given set of options.
 */
interface ContainerDefinitionCompiler {

    /**
     * Generate a ContainerDefinition defined by the $containerDefinitionCompileOptions.
     *
     * Throw an InvalidCompileOptionsException if some option passed is not valid or would result in an inability
     * to properly parse a ContainerDefinition.
     *
     * Throw an InvalidAnnotationException if some source code is annotated in such a way that a compilation error
     * occurs.
     *
     * @param ContainerDefinitionCompileOptions $containerDefinitionCompileOptions
     * @return ContainerDefinition
     */
    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions) : ContainerDefinition;

}