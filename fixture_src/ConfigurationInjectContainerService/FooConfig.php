<?php

namespace Cspray\AnnotatedContainerFixture\ConfigurationInjectContainerService;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;

#[Configuration]
class FooConfig {

    #[Inject(FooService::class)]
    public readonly FooService $foo;

}