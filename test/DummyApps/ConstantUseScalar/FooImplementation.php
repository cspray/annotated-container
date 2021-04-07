<?php declare(strict_types=1);


namespace Cspray\AnnotatedInjector\DummyApps\ConstantUseScalar;

use Cspray\AnnotatedInjector\Attribute\UseScalar;
use Cspray\AnnotatedInjector\Attribute\Service;

const FOO_BAR = 'foo_bar_val';

#[Service]
class FooImplementation {

    public function __construct(
        #[UseScalar(FOO_BAR)]
        public string $val
    ) {}

}