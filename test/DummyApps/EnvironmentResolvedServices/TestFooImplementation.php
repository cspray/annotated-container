<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\EnvironmentResolvedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(environments: ['test'])]
class TestFooImplementation implements FooInterface {

}