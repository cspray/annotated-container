<?php declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$compiler = new \Cspray\AnnotatedInjector\InjectorDefinitionCompiler();
$injectorDefinition = $compiler->compileDirectory(__DIR__ . '/src', 'dev');
$injector = \Cspray\AnnotatedInjector\AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

$subject = $injector->make(\Acme\AnnotatedInjectorDemo\MixedDefinesConstructorInjection::class);

var_dump($subject);