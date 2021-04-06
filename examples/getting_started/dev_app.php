<?php declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$compiler = new \Cspray\AnnotatedInjector\PhpParserInjectorDefinitionCompiler();
$injectorDefinition = $compiler->compileDirectory('dev', __DIR__ . '/src');
$injector = (new Cspray\AnnotatedInjector\AurynInjectorFactory)->createContainer($injectorDefinition);

$blobStorage = $injector->make(\Acme\AnnotatedInjectorDemo\BlobStorage::class);

$blobStorage->store('example blob');