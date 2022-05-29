<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\InjectCustomStoreServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooImplementation {

    public function __construct(
        #[Inject('key', from: 'test-store')] public readonly string $key
    ) {}

}