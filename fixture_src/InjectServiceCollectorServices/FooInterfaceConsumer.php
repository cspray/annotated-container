<?php

namespace Cspray\AnnotatedContainerFixture\InjectServiceCollectorServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooInterfaceConsumer {

    public function __construct(
        #[Inject(FooInterface::class, from: 'service-collector')]
        public readonly array $foos
    ) {}

}