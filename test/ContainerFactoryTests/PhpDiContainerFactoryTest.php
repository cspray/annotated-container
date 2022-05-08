<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactoryTests;

use Cspray\AnnotatedContainer\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\PhpDiContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactoryTestCase;
use Cspray\Typiphy\ObjectType;
use DI\Container;
use function Cspray\Typiphy\objectType;

class PhpDiContainerFactoryTest extends ContainerFactoryTestCase {

    protected function getContainerFactory() : ContainerFactory {
        return new PhpDiContainerFactory();
    }

    protected function getBackingContainerInstanceOf() : ObjectType {
        return objectType(Container::class);
    }
}