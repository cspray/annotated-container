<?php

namespace Cspray\AnnotatedContainerFixture;

use Cspray\AnnotatedContainerFixture\ConfigurationWithEnum\MyEnum;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class ConfigurationWithEnumFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ConfigurationWithEnum';
    }

    public function configuration() : ObjectType {
        return objectType(ConfigurationWithEnum\MyConfig::class);
    }

    public function fooEnum() : MyEnum {
        return MyEnum::Foo;
    }

    public function barEnum() : MyEnum {
        return MyEnum::Bar;
    }

}
