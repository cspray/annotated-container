<?php

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class ConstructorPromotedConfigurationFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ConstructorPromotedConfiguration';
    }

    public function constructorConfig() : ObjectType {
        return objectType(ConstructorPromotedConfiguration\ConstructorConfig::class);
    }
}