<?php

namespace Cspray\AnnotatedContainer;

class ConstantAnnotationValue implements AnnotationValue {

    public function __construct(private string $constantName) {}

    public function getCompileValue(): string|int|float|bool|array {
        return $this->constantName;
    }

    public function getRuntimeValue(): string|int|float|bool|array {
        return constant($this->constantName);
    }
}