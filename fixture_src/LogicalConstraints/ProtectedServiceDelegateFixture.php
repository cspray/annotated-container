<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints;

use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\ProtectedServiceDelegateMethod\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class ProtectedServiceDelegateFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ProtectedServiceDelegateMethod';
    }

    public function fooService() : ObjectType {
        return objectType(FooService::class);
    }
}