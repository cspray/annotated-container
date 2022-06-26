<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\NamedProfileResolvedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(profiles: ['test'], name: 'test-foo')]
class TestFooImplementation implements FooInterface {

}