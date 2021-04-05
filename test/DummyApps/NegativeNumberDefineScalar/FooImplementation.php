<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\NegativeNumberDefineScalar;

use Cspray\AnnotatedInjector\Attribute\DefineScalar;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class FooImplementation {

    public function __construct(
        #[DefineScalar(-1)]
        public int $intParam,
        #[DefineScalar(-42.0)]
        public float $floatParam
    ) {}

}