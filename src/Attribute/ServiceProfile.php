<?php

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ServiceProfile {

    private array $profiles;

    public function __construct(array $profiles) {
        $this->profiles = $profiles;
    }

}