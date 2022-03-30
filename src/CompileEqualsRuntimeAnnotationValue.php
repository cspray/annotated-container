<?php

namespace Cspray\AnnotatedContainer;

class CompileEqualsRuntimeAnnotationValue implements AnnotationValue {

    public function __construct(private string|int|float|bool|array $value) {}

    public function getCompileValue(): string|int|float|bool|array {
        return $this->value;
    }

    public function getRuntimeValue(): string|int|float|bool|array {
        return $this->value;
    }
}