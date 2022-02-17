<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\NegativeNumberUseScalar;

use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooImplementation {

    public function __construct(
        #[InjectScalar(-1)]
        public int $intParam,
        #[InjectScalar(-42.0)]
        public float $floatParam
    ) {}

}