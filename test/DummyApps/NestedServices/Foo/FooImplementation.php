<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\NestedServices\Foo;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\DummyApps\NestedServices\FooInterface;

#[Service]
class FooImplementation implements FooInterface {

}