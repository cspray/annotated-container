<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\InjectNamedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(name: 'foo')]
class FooImplementation implements FooInterface {

}