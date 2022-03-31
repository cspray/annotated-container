<?php

namespace Cspray\AnnotatedContainer;

use JsonSerializable;

final class JsonSerializableAnnotationValue implements JsonSerializable {

    public function __construct(private AnnotationValue $annotationValue) {}

    public function jsonSerialize(): array {
        return $this->getJsonForAnnotationValue($this->annotationValue);
    }

    private function getJsonForAnnotationValue(AnnotationValue $annotationValue) : array {
        if (is_array($annotationValue->getCompileValue())) {
            $compiledJson = [
                'type' => get_class($annotationValue),
                'items' => []
            ];
            foreach ($annotationValue->getCompileValue() as $value) {
                $compiledJson['items'][] = $this->getJsonForAnnotationValue($value);
            }
            return $compiledJson;
        } else {
            return [
                'type' => get_class($annotationValue),
                'value' => $annotationValue->getCompileValue()
            ];
        }
    }
}