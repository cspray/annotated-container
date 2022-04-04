<?php

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;
use Cspray\AnnotatedContainer\ScalarType;

#[Attribute(Attribute::TARGET_CLASS)]
final class ThirdPartyInjectService {

    public function __construct(string $type, string $method, string $paramName, string $paramType, string $value) {}

}