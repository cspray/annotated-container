<?php

namespace Cspray\AnnotatedContainer\Helper;

use Cspray\AnnotatedContainer\Compile\ContainerDefinitionCompileOptions;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

final class StubContainerDefinitionCompiler implements ContainerDefinitionCompiler {

    public function __construct(
        private readonly ContainerDefinition $containerDefinition
    ) {
    }

    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions) : ContainerDefinition {
        return $this->containerDefinition;
    }
}