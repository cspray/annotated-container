<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface ContainerDefinitionCompiler {

    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions) : ContainerDefinition;

}