<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class InjectCustomStoreServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectCustomStoreServices';
    }

    public function fooImplementation() : ObjectType {
        return objectType(InjectCustomStoreServices\FooImplementation::class);
    }
}