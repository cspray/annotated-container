<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\NamedProfileResolvedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(profiles: ['prod'], name: 'prod-foo')]
class ProdFooImplementation implements FooInterface {

}