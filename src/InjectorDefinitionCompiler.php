<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface InjectorDefinitionCompiler {

    public function compileDirectory(string $environment, array|string $dirs) : InjectorDefinition;

}