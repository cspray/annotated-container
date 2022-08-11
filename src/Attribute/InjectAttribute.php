<?php

namespace Cspray\AnnotatedContainer\Attribute;

use UnitEnum;

interface InjectAttribute {

    public function getValue() : string|int|float|bool|array|UnitEnum|null;

    public function getProfiles() : array;

    public function getFrom() : ?string;

}