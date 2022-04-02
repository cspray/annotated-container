<?php

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class ServiceDelegate {

    public function __construct(private string $service) {}

}