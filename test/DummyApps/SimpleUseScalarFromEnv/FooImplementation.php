<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\SimpleUseScalarFromEnv;

use Cspray\AnnotatedContainer\Attribute\InjectEnv;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooImplementation {

    public function __construct(
        #[InjectEnv('USER')]
        public string $user
    ) {}

}