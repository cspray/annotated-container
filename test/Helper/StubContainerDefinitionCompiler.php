<?php

namespace Cspray\AnnotatedContainer\Helper;

use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptions;
use Cspray\AnnotatedContainer\ContainerDefinitionCompiler;

final class StubContainerDefinitionCompiler implements ContainerDefinitionCompiler {

    public function __construct(
        private readonly ContainerDefinition $containerDefinition
    ) {
    }

    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions) : ContainerDefinition {
        return $this->containerDefinition;
    }
}