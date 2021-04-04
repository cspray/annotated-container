<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\NestedServices\Foo\Bar\Baz;

use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\DummyApps\NestedServices\BazInterface;

#[Service]
class BazImplementation implements BazInterface {

}