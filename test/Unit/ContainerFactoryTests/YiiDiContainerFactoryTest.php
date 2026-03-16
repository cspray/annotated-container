<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\ContainerFactoryTests;

use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\YiiDiContainerFactory;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use Cspray\AnnotatedContainer\Unit\ContainerFactoryTestCase;
use Cspray\Typiphy\ObjectType;
use Yiisoft\Di\Container;
use function Cspray\Typiphy\objectType;

class YiiDiContainerFactoryTest extends ContainerFactoryTestCase {

    protected function getContainerFactory(ActiveProfiles $activeProfiles): ContainerFactory {
        return new YiiDiContainerFactory();
    }

    protected function getBackingContainerInstanceOf(): ObjectType {
        return objectType(Container::class);
    }
}
