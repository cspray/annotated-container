<?php

namespace Cspray\AnnotatedContainer;

use UnitEnum;

/**
 * A value, typically associated with an argument to an Attribute, that cannot have its value determined at compile time.
 *
 * Constants and environment variables may be dynamic based on the machine that's running AnnotatedContainer. Because a key
 * goal is to be able to compile a ContainerDefinition for any set of profiles on any machine we need a way to represent
 * a value that should be deferred to when the container is actually created from the ContainerDefinition.
 */
interface AnnotationValue {

    public function getCompileValue() : string|int|float|bool|array;

    /**
     *
     *
     * @return string|int|float|bool|array|UnitEnum
     */
    public function getRuntimeValue() : string|int|float|bool|array|UnitEnum;

    public function __serialize() : array;

    public function __unserialize(array $data) : void;

}