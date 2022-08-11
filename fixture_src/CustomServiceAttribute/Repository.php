<?php

namespace Cspray\AnnotatedContainerFixture\CustomServiceAttribute;

use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Repository implements ServiceAttribute {

    public function getProfiles() : array {
        return ['test'];
    }

    public function isPrimary() : bool {
        return false;
    }

    public function getName() : ?string {
        return null;
    }
}