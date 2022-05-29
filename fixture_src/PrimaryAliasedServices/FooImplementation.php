<?php

namespace Cspray\AnnotatedContainerFixture\PrimaryAliasedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(primary: true)]
class FooImplementation implements FooInterface {

}