<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\AnnotationValue;

final class EnvironmentAnnotationValue implements AnnotationValue {

    public function __construct(private string $envVar) {}

    public function getCompileValue(): string {
        return $this->envVar;
    }

    public function getRuntimeValue(): string|int|float|bool|array {
        return getenv($this->envVar);
    }

    public function __serialize(): array {
        return [
            'envVar' => $this->envVar
        ];
    }

    public function __unserialize(array $data): void {
        $this->envVar = $data['envVar'];
    }
}