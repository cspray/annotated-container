<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface ContainerDefinitionCompiler {

    public function compileDirectory(string $environment, array|string $dirs) : ContainerDefinition;

}