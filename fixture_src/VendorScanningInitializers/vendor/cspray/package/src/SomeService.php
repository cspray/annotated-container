<?php

namespace Cspray\AnnotatedContainerFixture\VendorScanningInitializers;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface SomeService {

    public function setSomething(string $something) : void;

    public function getSomething() : string;

}