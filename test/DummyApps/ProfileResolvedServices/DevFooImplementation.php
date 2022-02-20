<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\DummyApps\ProfileResolvedServices;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceProfile;

#[Service]
#[ServiceProfile(['dev'])]
class DevFooImplementation implements FooInterface {

}