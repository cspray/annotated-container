<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\SimpleDefineScalarFromEnv;

use Cspray\AnnotatedInjector\Attribute\DefineScalarFromEnv;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class FooImplementation {

    public function __construct(
        #[DefineScalarFromEnv('USER')]
        public string $user
    ) {}

}