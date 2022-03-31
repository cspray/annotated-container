<?php

namespace Cspray\AnnotatedContainer;

final class ArrayAnnotationValue implements AnnotationValue {

    private array $annotationValues;

    public function __construct(AnnotationValue... $annotationValues) {
        $this->annotationValues = $annotationValues;
    }

    public function getCompileValue(): array {
        return $this->annotationValues;
    }

    public function getRuntimeValue(): array {
        $values = [];
        foreach ($this->annotationValues as $annotationValue) {
            $values[] = $annotationValue->getRuntimeValue();
        }
        return $values;
    }
}