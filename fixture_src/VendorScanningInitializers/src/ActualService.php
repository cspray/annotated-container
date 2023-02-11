<?php

namespace Cspray\AnnotatedContainerFixture\VendorScanningInitializers;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class ActualService implements SomeService {

    private string $something = '';

    public function __construct(
        public readonly ThirdPartyDependency $thirdPartyDependency
    ) {}

    public function setSomething(string $something) : void {
        $this->something = $something;
    }

    public function getSomething() : string {
        return $this->something;
    }
}