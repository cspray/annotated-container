<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\NestedServices\Foo\Bar;

use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\DummyApps\NestedServices\BarInterface;

#[Service]
class BarImplementation implements BarInterface {

}