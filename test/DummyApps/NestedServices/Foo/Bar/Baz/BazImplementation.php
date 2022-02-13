<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\NestedServices\Foo\Bar\Baz;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\DummyApps\NestedServices\BazInterface;

#[Service]
class BazImplementation implements BazInterface {

}