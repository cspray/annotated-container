<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\DuplicateNamedServiceDifferentProfiles;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(profiles: ['dev'], name: 'service')]
class FooService {

}