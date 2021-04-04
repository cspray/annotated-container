<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class DefineScalar {

    public function __construct(
        private string|int|float|bool|array|null $value = null,
        private ?string $envVar = null
    ) {}

}