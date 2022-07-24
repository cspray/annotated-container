<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactoryTests;

use Amp\Injector\Application;
use Cspray\AnnotatedContainer\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\AmphpContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactoryTestCase;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class AmphpContainerFactoryTest extends ContainerFactoryTestCase {

    protected function getContainerFactory() : ContainerFactory {
        return new AmphpContainerFactory();
    }

    protected function getBackingContainerInstanceOf() : ObjectType {
        return objectType(Application::class);
    }
}