<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\Typiphy\ObjectType;
use UnitEnum;

/**
 * @Internal
 */
final class Objects {

    private function __construct() {}

    /**
     * @param class-string|ObjectType $type
     * @return bool
     */
    public static function isEnum(string|ObjectType $type) : bool {
        $type = is_string($type) ? $type : $type->getName();
        return is_a($type, UnitEnum::class, true);
    }

}