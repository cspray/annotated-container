<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\ClassConstantUseScalar;

use Cspray\AnnotatedInjector\Attribute\UseScalar;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class FooImplementation {

    public const VALUE = 'foo_val';

    public function __construct(
        #[UseScalar(FooImplementation::VALUE)]
        public string $val
    ) {}

}