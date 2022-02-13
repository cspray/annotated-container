<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\SimpleUseScalar;

use Cspray\AnnotatedContainer\Attribute\UseScalar;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooImplementation {

    public function __construct(
        #[UseScalar('string param test value')]
        public string $stringParam,
        #[UseScalar(42)]
        public int $intParam,
        #[UseScalar(42.0)]
        public float $floatParam,
        #[UseScalar(true)]
        public bool $boolParam,
        #[UseScalar([
            ['a', 'b', 'c'],
            [1, 2, 3],
            [1.1, 2.1, 3.1],
            [true, false, true],
            [['a', 'b', 'c'], [1, 2, 3], [1.1, 2.1, 3.1], [true, false, true]]
        ])]
        public array $arrayParam
    ) {}

}