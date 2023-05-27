<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints\DuplicateServiceName;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(name: 'foo')]
class FooService {

}