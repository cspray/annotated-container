<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\ClassConstantUseScalar;

use Cspray\AnnotatedContainer\Attribute\UseScalar;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooImplementation {

    public const VALUE = 'foo_val';

    public function __construct(
        #[UseScalar(FooImplementation::VALUE)]
        public string $val
    ) {}

}