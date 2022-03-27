<?php

namespace Cspray\AnnotatedContainer\Internal;

/**
 * @Internal
 */
final class AnnotationArguments {

    private array $map = [];

    public function put(string $key, mixed $value) : void {
        $this->map[$key] = $value;
    }

    public function get(string $key, mixed $default = null) : mixed {
        return $this->map[$key] ?? $default;
    }

    public function has(string $key) : bool {
        return isset($this->map[$key]);
    }

}