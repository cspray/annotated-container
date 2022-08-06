<?php

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class ConfigurationWithArrayEnumFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ConfigurationWithArrayEnum';
    }

    public function myConfiguration() : ObjectType {
        return objectType(ConfigurationWithArrayEnum\MyConfiguration::class);
    }

}
