<?php

namespace Cspray\AnnotatedContainer\Attribute;

use Cspray\Typiphy\ObjectType;

interface ServiceAttribute {

    /**
     * @return list<string>
     */
    public function getProfiles() : array;

    public function isPrimary() : bool;

    public function getName() : ?string;

}