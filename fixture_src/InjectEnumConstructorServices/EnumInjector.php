<?php

namespace Cspray\AnnotatedContainerFixture\InjectEnumConstructorServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class EnumInjector {

    public function __construct(
        #[Inject(CardinalDirections::North)]
        public readonly CardinalDirections $directions
    ) {}

}