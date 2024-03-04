<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\ContainerFactoryTests;

use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\PhpDiContainerFactory;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Unit\ContainerFactoryTestCase;
use Cspray\Typiphy\ObjectType;
use DI\Container;
use function Cspray\Typiphy\objectType;

class PhpDiContainerFactoryTest extends ContainerFactoryTestCase {

    protected function getContainerFactory(Emitter $emitter = new Emitter()) : ContainerFactory {
        return new PhpDiContainerFactory(emitter: $emitter);
    }

    protected function getBackingContainerInstanceOf() : ObjectType {
        return objectType(Container::class);
    }
}