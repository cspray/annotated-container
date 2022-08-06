<?php

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class ConfigurationWithAssocArrayEnumFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ConfigurationWithAssocArrayEnum';
    }

    public function myConfiguration() : ObjectType {
        return objectType(ConfigurationWithAssocArrayEnum\MyConfiguration::class);
    }
}
