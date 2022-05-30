<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\MultiPropConfigurationServices;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;

#[Configuration]
class MyConfig {

    #[Inject('baz')]
    private readonly string $foo, $bar;

}