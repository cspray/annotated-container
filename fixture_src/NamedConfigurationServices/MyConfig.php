<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\NamedConfigurationServices;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;

#[Configuration(name: 'my-config')]
class MyConfig {

    #[Inject('my-api-key')]
    public readonly string $key;

    #[Inject('my-api-secret')]
    public readonly string $secret;

}