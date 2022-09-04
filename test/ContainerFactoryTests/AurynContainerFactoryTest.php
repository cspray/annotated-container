<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactoryTests;

use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\AurynContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactoryTestCase;
use Cspray\Typiphy\ObjectType;
use Auryn\Injector;
use function Cspray\Typiphy\objectType;

class AurynContainerFactoryTest extends ContainerFactoryTestCase {

    protected function getContainerFactory() : ContainerFactory {
        return new AurynContainerFactory();
    }

    protected function getBackingContainerInstanceOf() : ObjectType {
        return objectType(Injector::class);
    }
}