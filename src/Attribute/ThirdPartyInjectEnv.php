<?php

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;
use Cspray\AnnotatedContainer\ScalarType;

#[Attribute(Attribute::TARGET_CLASS)]
final class ThirdPartyInjectEnv {

    public function __construct(string $type, string $method, string $paramName, ScalarType $paramType, string $value, array $profiles = []) {}

}