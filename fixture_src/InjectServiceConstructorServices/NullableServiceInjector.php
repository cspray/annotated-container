<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\InjectServiceConstructorServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class NullableServiceInjector {

    public function __construct(
        #[Inject(null)] public readonly ?FooInterface $maybeFoo
    ) {}

}