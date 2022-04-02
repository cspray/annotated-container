<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\AnnotationValue;
use Cspray\AnnotatedContainer\ArrayAnnotationValue;
use Cspray\AnnotatedContainer\CompileEqualsRuntimeAnnotationValue;

/**
 * @Internal
 */
final class AnnotationArguments {

    private array $map = [];

    public function put(string $key, AnnotationValue $value) : void {
        $this->map[$key] = $value;
    }

    public function get(string $key, string|int|float|bool|array $default = null) : AnnotationValue {
        if (!isset($this->map[$key])) {
            if (is_array($default)) {
                $values = [];
                foreach ($default as $value) {
                    $values[] = new CompileEqualsRuntimeAnnotationValue($value);
                }
                return new ArrayAnnotationValue(...$values);
            } else {
                return new CompileEqualsRuntimeAnnotationValue($default);
            }
        }
        return $this->map[$key];
    }

    public function has(string $key) : bool {
        return isset($this->map[$key]);
    }

}