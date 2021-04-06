<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

interface InjectorDefinitionCompiler {

    public function compileDirectory(string $environment, array|string $dirs) : InjectorDefinition;

}