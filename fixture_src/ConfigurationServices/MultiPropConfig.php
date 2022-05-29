<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\ConfigurationServices;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;

#[Configuration]
class MultiPropConfig {

    #[Inject('baz')]
    private readonly string $foo, $bar;

}