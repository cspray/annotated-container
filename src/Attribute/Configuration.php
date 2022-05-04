<?php

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Configuration {

    public function __construct(
        public readonly ?string $name = null
    ) {}

}