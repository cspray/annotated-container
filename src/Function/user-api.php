<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Auryn\Injector;
use Cspray\AnnotatedContainer\ContainerFactory\AurynContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\PhpDiContainerFactory;
use Cspray\AnnotatedContainer\Exception\ContainerFactoryNotFoundException;
use DI\Container;

function compiler(string $cacheDir = null) : ContainerDefinitionCompiler {
    if (is_null($cacheDir)) {
        return ContainerDefinitionCompilerBuilder::withoutCache()->build();
    } else {
        return ContainerDefinitionCompilerBuilder::withCache($cacheDir)->build();
    }
}

function containerFactory() : ContainerFactory {
    if (class_exists(Injector::class)) {
        return new AurynContainerFactory();
    } else if (class_exists(Container::class)) {
        return new PhpDiContainerFactory();
    } else {
        throw new ContainerFactoryNotFoundException('There is no backing Container library found. Please run "composer suggests" for supported containers.');
    }
}
