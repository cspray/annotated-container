<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\ProfileResolvedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(profiles: ['prod'])]
class ProdFooImplementation implements FooInterface {

}