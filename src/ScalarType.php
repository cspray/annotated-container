<?php

namespace Cspray\AnnotatedContainer;

enum ScalarType {
    case String;
    case Int;
    case Float;
    case Bool;
    case Array;

    public static function fromName(string $name) : ScalarType {
        $name = strtolower($name);
        if ($name === 'string') {
            return ScalarType::String;
        } else if ($name === 'int') {
            return ScalarType::Int;
        } else if ($name === 'float') {
            return ScalarType::Float;
        } else if ($name === 'bool') {
            return ScalarType::Bool;
        } else if ($name === 'array') {
            return ScalarType::Array;
        }

        throw new \InvalidArgumentException('An invalid ScalarType, ' . $name . ', name has been given.');
    }
}