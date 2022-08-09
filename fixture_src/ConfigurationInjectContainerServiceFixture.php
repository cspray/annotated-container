<?php

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class ConfigurationInjectContainerServiceFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ConfigurationInjectContainerService';
    }

    public function fooService() : ObjectType {
        return objectType(ConfigurationInjectContainerService\FooService::class);
    }

    public function fooConfig() : ObjectType {
        return objectType(ConfigurationInjectContainerService\FooConfig::class);
    }

}
