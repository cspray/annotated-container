<?php

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class ThirdPartyServiceDelegate {

    public function __construct(string $type, string $factoryClass, string $factoryMethod) {}

}