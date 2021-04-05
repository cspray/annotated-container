<?php declare(strict_types=1);


namespace Cspray\AnnotatedInjector\DummyApps\ConstantDefineScalar;

use Cspray\AnnotatedInjector\Attribute\DefineScalar;
use Cspray\AnnotatedInjector\Attribute\Service;

const FOO_BAR = 'foo_bar_val';

#[Service]
class FooImplementation {

    public function __construct(
        #[DefineScalar(FOO_BAR)]
        public string $val
    ) {}

}