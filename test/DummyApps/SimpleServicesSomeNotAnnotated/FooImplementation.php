<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\SimpleServicesSomeNotAnnotated;

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class FooImplementation implements FooInterface {

}