<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainerFixture\AmbiguousAliasedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class BazImplementation implements FooInterface {

}