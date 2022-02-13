<?php declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$compiler = new \Cspray\AnnotatedContainer\PhpParserContainerDefinitionCompiler();
$injectorDefinition = $compiler->compileDirectory('prod', __DIR__ . '/src');
$injector = (new Cspray\AnnotatedContainer\AurynInjectorFactory)->createInjector($injectorDefinition);

$blobStorage = $injector->make(\Acme\AnnotatedContainerDemo\BlobStorage::class);

$blobStorage->store('example blob');