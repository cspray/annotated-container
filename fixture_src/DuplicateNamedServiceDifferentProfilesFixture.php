<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\AnnotatedContainerFixture\DuplicateNamedServiceDifferentProfiles\BarService;
use Cspray\AnnotatedContainerFixture\DuplicateNamedServiceDifferentProfiles\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class DuplicateNamedServiceDifferentProfilesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateNamedServiceDifferentProfiles';
    }

    public function barService() : ObjectType {
        return objectType(BarService::class);
    }

    public function fooService() : ObjectType {
        return objectType(FooService::class);
    }

}
