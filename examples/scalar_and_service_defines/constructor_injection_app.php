<?php declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$compiler = new \Cspray\AnnotatedInjector\PhpParserInjectorDefinitionCompiler();
$injectorDefinition = $compiler->compileDirectory('dev', __DIR__ . '/src');
$injector = \Cspray\AnnotatedInjector\AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

$subject = $injector->make(\Acme\AnnotatedInjectorDemo\MixedDefinesConstructorInjection::class);

var_dump($subject);