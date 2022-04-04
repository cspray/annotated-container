<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\AnnotationValue;

final class SingleAnnotationValue implements AnnotationValue {

    public function __construct(private string|int|float|bool $value) {}

    public function getCompileValue(): string|int|float|bool {
        return $this->value;
    }

    public function getRuntimeValue(): string|int|float|bool {
        return $this->value;
    }

    public function __serialize() : array {
        return ['value' => $this->value];
    }

    public function __unserialize(array $data) : void {
        $this->value = $data['value'];
    }

}