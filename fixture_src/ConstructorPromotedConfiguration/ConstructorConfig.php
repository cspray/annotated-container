<?php

namespace Cspray\AnnotatedContainerFixture\ConstructorPromotedConfiguration;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;

#[Configuration]
final class ConstructorConfig {

    public function __construct(
        #[Inject('bar')]
        public readonly string $foo,
        #[Inject(42)]
        public readonly int $bar
    ) {}

}