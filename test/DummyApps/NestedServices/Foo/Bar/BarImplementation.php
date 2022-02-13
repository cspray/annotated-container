<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\NestedServices\Foo\Bar;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\DummyApps\NestedServices\BarInterface;

#[Service]
class BarImplementation implements BarInterface {

}