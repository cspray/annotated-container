<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\SimpleUseScalar;

use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooImplementation {

    public function __construct(
        #[InjectScalar('string param test value')]
        public string $stringParam,
        #[InjectScalar(42)]
        public int $intParam,
        #[InjectScalar(42.0)]
        public float $floatParam,
        #[InjectScalar(true)]
        public bool $boolParam,
        #[InjectScalar([
            ['a', 'b', 'c'],
            [1, 2, 3],
            [1.1, 2.1, 3.1],
            [true, false, true],
            [['a', 'b', 'c'], [1, 2, 3], [1.1, 2.1, 3.1], [true, false, true]]
        ])]
        public array $arrayParam
    ) {}

}