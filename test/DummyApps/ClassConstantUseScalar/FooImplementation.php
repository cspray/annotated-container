<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\ClassConstantUseScalar;

use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooImplementation {

    public const VALUE = 'foo_val';

    public function __construct(
        #[InjectScalar(FooImplementation::VALUE)]
        public string $val
    ) {}

}