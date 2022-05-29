<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class InterfacePrepareServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InterfacePrepareServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(InterfacePrepareServices\FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(InterfacePrepareServices\FooImplementation::class);
    }

}