<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class NamedConfigurationServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/NamedConfigurationServices';
    }

    public function myConfig() : ObjectType {
        return objectType(NamedConfigurationServices\MyConfig::class);
    }
}