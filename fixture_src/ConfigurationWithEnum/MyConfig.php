<?php

namespace Cspray\AnnotatedContainerFixture\ConfigurationWithEnum;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;

#[Configuration]
final class MyConfig {

    #[Inject(MyEnum::Foo)]
    public readonly MyEnum $enum;

}