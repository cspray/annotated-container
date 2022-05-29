<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class NonSharedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/NonSharedServices';
    }

    public function fooImplementation() : ObjectType {
        return objectType(NonSharedServices\FooImplementation::class);
    }
}