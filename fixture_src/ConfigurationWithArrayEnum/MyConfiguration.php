<?php

namespace Cspray\AnnotatedContainerFixture\ConfigurationWithArrayEnum;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;

#[Configuration]
class MyConfiguration {

    #[Inject([FooEnum::Bar, FooEnum::Qux])]
    public readonly array $cases;

}