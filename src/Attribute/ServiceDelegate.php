<?php

namespace Cspray\AnnotatedInjector\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class ServiceDelegate {

    private string $forService;

    public function __construct(string $forService) {
        $this->forService = $forService;
    }

    public function getForService() : string {
        return $this->forService;
    }

}