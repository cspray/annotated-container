<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class MultiPropConfigurationServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/MultiPropConfigurationServices';
    }

    public function myConfig() : ObjectType {
        return objectType(MultiPropConfigurationServices\MyConfig::class);
    }

}