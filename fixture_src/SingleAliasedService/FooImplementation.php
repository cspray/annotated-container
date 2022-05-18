<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\SingleAliasedService;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooImplementation implements FooInterface {

}