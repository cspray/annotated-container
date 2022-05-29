<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\SubNamespacedServices\Foo;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainerFixture\SubNamespacedServices\FooInterface;

#[Service]
class FooImplementation implements FooInterface {

}