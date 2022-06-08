<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\InjectServiceIntersectConstructorServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooBarImplementation implements FooInterface, BarInterface {

}