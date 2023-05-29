<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints;

use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\DuplicateServicePrepare\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class DuplicateServicePrepareFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateServicePrepare';
    }

    public function fooService() : ObjectType {
        return objectType(FooService::class);
    }

}
