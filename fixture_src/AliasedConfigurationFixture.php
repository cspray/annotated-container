<?php

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class AliasedConfigurationFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/AliasedConfiguration';
    }

    public function appConfig() : ObjectType {
        return objectType(AliasedConfiguration\AppConfig::class);
    }

    public function myAppConfig() : ObjectType {
        return objectType(AliasedConfiguration\MyAppConfig::class);
    }
}