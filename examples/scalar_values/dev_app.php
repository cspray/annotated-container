<?php declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$compiler = new \Cspray\AnnotatedInjector\InjectorDefinitionCompiler();
$injectorDefinition = $compiler->compileDirectory(__DIR__ . '/src', 'dev');
$injector = \Cspray\AnnotatedInjector\AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

$scalarGetter = $injector->make(\Acme\AnnotatedInjectorDemo\ScalarGetter::class);

var_dump($scalarGetter);
