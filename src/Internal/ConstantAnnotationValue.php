<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\AnnotationValue;

final class ConstantAnnotationValue implements AnnotationValue {

    public function __construct(private string $name) {}

    public function getCompileValue(): string {
        return $this->name;
    }

    public function getRuntimeValue(): string|int|float|bool|array {
        return constant($this->name);
    }

    public function __serialize(): array {
        return [
            'name' => $this->name
        ];
    }

    public function __unserialize(array $data): void {
        $this->name = $data['name'];
    }

}