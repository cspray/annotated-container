<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\NegativeNumberUseScalar;

use Cspray\AnnotatedInjector\Attribute\UseScalar;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class FooImplementation {

    public function __construct(
        #[UseScalar(-1)]
        public int $intParam,
        #[UseScalar(-42.0)]
        public float $floatParam
    ) {}

}