<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\DummyApps\EnvironmentResolvedServices;

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service(environments: ['prod'])]
class ProdFooImplementation implements FooInterface {

}