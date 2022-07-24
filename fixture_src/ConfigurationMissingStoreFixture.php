<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class ConfigurationMissingStoreFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ConfigurationMissingStore';
    }

    public function myConfig() : ObjectType {
        return objectType(ConfigurationMissingStore\MyConfig::class);
    }

}