<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\NestedServices\Foo;

use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\DummyApps\NestedServices\FooInterface;

#[Service]
class FooImplementation implements FooInterface {

}