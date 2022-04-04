<?php

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class ThirdPartyServicePrepare {

    public function __construct(string $type, string $method) {}

}