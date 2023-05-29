<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints;

use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\MultiplePrimaryService\BarService;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\MultiplePrimaryService\FooInterface;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\MultiplePrimaryService\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class MultiplePrimaryServiceFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/MultiplePrimaryService';
    }

    public function fooInterface() : ObjectType {
        return objectType(FooInterface::class);
    }

    public function fooService() : ObjectType {
        return objectType(FooService::class);
    }

    public function barService() : ObjectType {
        return objectType(BarService::class);
    }

}
