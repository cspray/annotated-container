<?php

namespace Cspray\AnnotatedContainer\Attribute;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class ThirdPartyService {

    public function __construct(string $type, string $name = null, array $profiles = [], bool $isPrimary = false) {}

}