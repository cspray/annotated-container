<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalErrorApps\DuplicateServiceName;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(name: 'foo')]
class FooService {

}