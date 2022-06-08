<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\InjectServiceIntersectUnionServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooBarImplementation implements FooInterface, BarInterface {

}