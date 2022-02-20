<?php

namespace Cspray\AnnotatedContainer\DummyApps\MultipleImplementedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooBarImplementation implements BarInterface, FooInterface {

}