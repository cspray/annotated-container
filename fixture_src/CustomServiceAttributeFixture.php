<?php

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class CustomServiceAttributeFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/CustomServiceAttribute';
    }

    public function myRepo() : ObjectType {
        return objectType(CustomServiceAttribute\MyRepo::class);
    }
}