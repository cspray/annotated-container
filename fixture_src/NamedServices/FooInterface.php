<?php

namespace Cspray\AnnotatedContainerFixture\NamedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(name: 'foo')]
interface FooInterface {}