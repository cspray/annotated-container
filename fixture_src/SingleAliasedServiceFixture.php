<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class SingleAliasedServiceFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/SingleAliasedService';
    }

    public function fooInterface() : ObjectType {
        return objectType(SingleAliasedService\FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(SingleAliasedService\FooImplementation::class);
    }

}