<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\NegativeNumberUseScalar;

use Cspray\AnnotatedContainer\Attribute\UseScalar;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooImplementation {

    public function __construct(
        #[UseScalar(-1)]
        public int $intParam,
        #[UseScalar(-42.0)]
        public float $floatParam
    ) {}

}