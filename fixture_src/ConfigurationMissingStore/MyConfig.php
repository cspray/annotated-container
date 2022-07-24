<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\ConfigurationMissingStore;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;

#[Configuration]
class MyConfig {

    #[Inject('key', from: 'test-store')]
    public readonly string $val;

}