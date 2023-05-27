<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints;

use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\DuplicateServiceType\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class DuplicateServiceTypeFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateServiceType';
    }

    public function fooService() : ObjectType {
        return objectType(FooService::class);
    }

}
