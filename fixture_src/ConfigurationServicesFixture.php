<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class ConfigurationServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ConfigurationServices';
    }

    public function myConfig() : ObjectType {
        return objectType(ConfigurationServices\MyConfig::class);
    }

    public function multiPropConfig() : ObjectType {
        return objectType(ConfigurationServices\MultiPropConfig::class);
    }

}