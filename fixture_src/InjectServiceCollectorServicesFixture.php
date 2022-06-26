<?php

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class InjectServiceCollectorServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectServiceCollectorServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(InjectServiceCollectorServices\FooInterface::class);
    }

    public function barImplementation() : ObjectType {
        return objectType(InjectServiceCollectorServices\BarImplementation::class);
    }

    public function bazImplementation() : ObjectType {
        return objectType(InjectServiceCollectorServices\BazImplementation::class);
    }

    public function quxImplementation() : ObjectType {
        return objectType(InjectServiceCollectorServices\QuxImplementation::class);
    }

    public function fooConsumer() : ObjectType {
        return objectType(InjectServiceCollectorServices\FooInterfaceConsumer::class);
    }
}