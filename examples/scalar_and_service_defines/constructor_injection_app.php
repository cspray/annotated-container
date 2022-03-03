<?php declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$compiler = new \Cspray\AnnotatedContainer\PhpParserInjectorDefinitionCompiler();
$injectorDefinition = $compiler->compileDirectory('dev', __DIR__ . '/src');
$injector = (new Cspray\AnnotatedContainer\AurynContainerFactory)->createContainer($injectorDefinition);

$subject = $injector->make(\Acme\AnnotatedContainerDemo\MixedDefinesConstructorInjection::class);

var_dump($subject);