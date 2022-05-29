<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\SubNamespacedServices\Foo\Bar\Baz;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainerFixture\SubNamespacedServices\BazInterface;

#[Service]
class BazImplementation implements BazInterface {

}