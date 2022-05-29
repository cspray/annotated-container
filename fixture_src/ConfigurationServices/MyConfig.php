<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\ConfigurationServices;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;

#[Configuration]
final class MyConfig {

    #[Inject('my-api-key')]
    public readonly string $key;

    #[Inject(1234)]
    public readonly int $port;

    #[Inject('USER', from: 'env')]
    public readonly string $user;

    #[Inject(true, profiles: ['dev', 'test'])]
    #[Inject(false, profiles: ['prod'])]
    public readonly bool $testMode;

}