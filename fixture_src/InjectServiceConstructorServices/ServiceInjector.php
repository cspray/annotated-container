<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\InjectServiceConstructorServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class ServiceInjector {

    public function __construct(
        #[Inject(FooImplementation::class)] public readonly FooInterface $foo
    ) {}

}