<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainer\DummyApps\ConstantUseScalar;

use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\Service;

const FOO_BAR = 'foo_bar_val';

#[Service]
class FooImplementation {

    public function __construct(
        #[InjectScalar(FOO_BAR)]
        public string $val
    ) {}

}