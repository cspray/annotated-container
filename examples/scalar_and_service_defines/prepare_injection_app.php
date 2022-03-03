<?php declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$compiler = new \Cspray\AnnotatedContainer\PhpParserContainerDefinitionCompiler();
$injectorDefinition = $compiler->compile('dev', __DIR__ . '/src');
$injector = (new Cspray\AnnotatedContainer\AurynContainerFactory)->createInjector($injectorDefinition);

$subject = $injector->make(\Acme\AnnotatedContainerDemo\MixedDefinesSetterInjection::class);

var_dump($subject);
