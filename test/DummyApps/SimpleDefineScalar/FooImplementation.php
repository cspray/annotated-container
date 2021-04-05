<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\SimpleDefineScalar;

use Cspray\AnnotatedInjector\Attribute\DefineScalar;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class FooImplementation {

    public function __construct(
        #[DefineScalar('string param test value')]
        public string $stringParam,
        #[DefineScalar(42)]
        public int $intParam,
        #[DefineScalar(42.0)]
        public float $floatParam,
        #[DefineScalar(true)]
        public bool $boolParam,
        #[DefineScalar([
            ['a', 'b', 'c'],
            [1, 2, 3],
            [1.0, 2.0, 3.0],
            [true, false, true],
            [['a', 'b', 'c'], [1, 2, 3], [1.0, 2.0, 3.0], [true, false, true]]
        ])]
        public array $arrayParam
    ) {}

}