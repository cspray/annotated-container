<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\LogicalConstraints\DuplicateServiceType;

use Attribute;
use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class DummyService implements ServiceAttribute {

    public function getProfiles() : array {
        return [];
    }

    public function isPrimary() : bool {
        return false;
    }

    public function getName() : ?string {
        return null;
    }
}