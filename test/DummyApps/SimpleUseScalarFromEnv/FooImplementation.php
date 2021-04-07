<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\SimpleUseScalarFromEnv;

use Cspray\AnnotatedInjector\Attribute\UseScalarFromEnv;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class FooImplementation {

    public function __construct(
        #[UseScalarFromEnv('USER')]
        public string $user
    ) {}

}