<?php

namespace Cspray\AnnotatedContainerFixture\NonSharedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(shared: false)]
class FooImplementation {

}