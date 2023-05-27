<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints\DuplicateServiceType;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service, DummyService]
class FooService {

}