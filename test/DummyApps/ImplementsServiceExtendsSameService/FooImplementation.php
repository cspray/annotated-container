<?php

namespace Cspray\AnnotatedContainer\DummyApps\ImplementsServiceExtendsSameService;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooImplementation extends AbstractFoo implements FooInterface {

}