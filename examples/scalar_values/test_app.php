<?php declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$compiler = new \Cspray\AnnotatedInjector\PhpParserInjectorDefinitionCompiler();
$injectorDefinition = $compiler->compileDirectory('test', __DIR__ . '/src');
$injector = (new Cspray\AnnotatedInjector\AurynInjectorFactory)->createContainer($injectorDefinition);

$scalarGetter = $injector->make(\Acme\AnnotatedInjectorDemo\ScalarGetter::class);

var_dump($scalarGetter);
