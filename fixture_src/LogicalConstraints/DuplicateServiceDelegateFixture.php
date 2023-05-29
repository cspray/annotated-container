<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints;

use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\DuplicateServiceDelegate\Factory;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\DuplicateServiceDelegate\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class DuplicateServiceDelegateFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateServiceDelegate';
    }

    public function fooService() : ObjectType {
        return objectType(FooService::class);
    }

    public function factory() : ObjectType {
        return objectType(Factory::class);
    }

}
