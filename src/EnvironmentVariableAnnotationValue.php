<?php

namespace Cspray\AnnotatedContainer;

class EnvironmentVariableAnnotationValue implements AnnotationValue {

    public function __construct(private string $envVar) {}

    public function getCompileValue(): string|int|float|bool|array {
        return $this->envVar;
    }

    public function getRuntimeValue(): string|int|float|bool|array {
        return getenv($this->envVar);
    }
}