<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class InjectServiceIntersectConstructorServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectServiceIntersectConstructorServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(InjectServiceIntersectConstructorServices\FooInterface::class);
    }

    public function barInterface() : ObjectType {
        return objectType(InjectServiceIntersectConstructorServices\BarInterface::class);
    }

    public function fooBarImplementation() : ObjectType {
        return objectType(InjectServiceIntersectConstructorServices\FooBarImplementation::class);
    }

    public function fooBarConsumer() : ObjectType {
        return objectType(InjectServiceIntersectConstructorServices\FooBarConsumer::class);
    }
}