<?php

namespace Cspray\AnnotatedContainer;

class ArrayAnnotationValue implements AnnotationValue {

    private array $annotationValues;

    public function __construct(AnnotationValue... $annotationValues) {
        $this->annotationValues = $annotationValues;
    }

    public function getCompileValue(): string|int|float|bool|array {
        return $this->annotationValues;
    }

    public function getRuntimeValue(): string|int|float|bool|array {
        $values = [];
        foreach ($this->annotationValues as $annotationValue) {
            $values[] = $annotationValue->getRuntimeValue();
        }
        return $values;
    }
}