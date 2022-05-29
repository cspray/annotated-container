<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\SubNamespacedServices\Foo\Bar;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainerFixture\SubNamespacedServices\BarInterface;

#[Service]
class BarImplementation implements BarInterface {

}