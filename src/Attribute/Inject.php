<?php

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class Inject {

    public function __construct(
        public readonly string|int|float|bool|array|UnitEnum|null $value,
        public readonly ?string $from = null,
        public readonly array $profiles = []
    ) {}

}