<?php

namespace Cspray\AnnotatedContainer\Attribute;

use UnitEnum;

interface InjectAttribute {

    public function getValue() : mixed;

    public function getProfiles() : array;

    public function getFrom() : ?string;

}