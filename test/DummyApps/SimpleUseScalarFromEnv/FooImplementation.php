<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\SimpleUseScalarFromEnv;

use Cspray\AnnotatedContainer\Attribute\UseScalarFromEnv;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooImplementation {

    public function __construct(
        #[UseScalarFromEnv('USER')]
        public string $user
    ) {}

}