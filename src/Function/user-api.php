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

function containerFactory(SupportedContainers $container = SupportedContainers::Default) : ContainerFactory {
    if ($container === SupportedContainers::Auryn || ($container === SupportedContainers::Default) && class_exists(Injector::class)) {
        static $auryn;
        if (!isset($auryn)) {
            $auryn = new AurynContainerFactory();
        }

        return $auryn;
    } else if ($container === SupportedContainers::PhpDi || ($container === SupportedContainers::Default && class_exists(Container::class))) {
        static $di;
        if (!isset($di)) {
            $di = new PhpDiContainerFactory();
        }

        return $di;
    } else {
        throw new ContainerFactoryNotFoundException('There is no backing Container library found. Please run "composer suggests" for supported containers.');
    }
}
