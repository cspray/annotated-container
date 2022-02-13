<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\SimpleServicesSomeNotAnnotated;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooImplementation implements FooInterface {

}