<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainerFixture\InjectServiceCollectorServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class BazImplementation implements FooInterface {

}