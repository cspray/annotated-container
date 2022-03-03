<?php

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class ServiceProfile {


    public function __construct(private array $profiles) {}

}