<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\ClassConstantDefineScalar;

use Cspray\AnnotatedInjector\Attribute\DefineScalar;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class FooImplementation {

    public const VALUE = 'foo_val';

    public function __construct(
        #[DefineScalar(FooImplementation::VALUE)]
        public string $val
    ) {}

}