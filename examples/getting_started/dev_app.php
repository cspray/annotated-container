<?php declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use Acme\AnnotatedContainerDemo\BlobStorage;
use Cspray\AnnotatedContainer\ContainerDefinitionCompilerFactory;
use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\AurynContainerFactory;

$compiler = ContainerDefinitionCompilerFactory::withoutCache()->getCompiler();
$containerDefinition = $compiler->compile(
    ContainerDefinitionCompileOptionsBuilder::scanDirectories('src')->withProfiles('default', 'dev')->build()
);
$injector = (new AurynContainerFactory)->createContainer($containerDefinition);

$blobStorage = $injector->get(BlobStorage::class);

$blobStorage->store('example blob');