<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints;

use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\DuplicateServiceName\BarService;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\DuplicateServiceName\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class DuplicateServiceNameFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateServiceName';
    }

    public function getBarService() : ObjectType {
        return objectType(BarService::class);
    }

    public function getFooService() : ObjectType {
        return objectType(FooService::class);
    }
}