<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class DefineScalarFromEnv {

    public function __construct(private string $envVar) {}

}