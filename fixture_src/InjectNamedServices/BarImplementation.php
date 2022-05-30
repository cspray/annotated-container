<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\InjectNamedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(name: 'bar')]
class BarImplementation implements FooInterface {

}