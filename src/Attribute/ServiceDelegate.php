<?php

namespace Cspray\AnnotatedContainer\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class ServiceDelegate {

    private string $forService;

    public function __construct(string $forService) {
        $this->forService = $forService;
    }

    public function getForService() : string {
        return $this->forService;
    }

}