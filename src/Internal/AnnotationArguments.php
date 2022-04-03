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
            } else if (!is_null($default)) {
                return new CompileEqualsRuntimeAnnotationValue($default);
            } else {
                throw new \RuntimeException('Provided a default null value for a key that is not present');
            }
        }
        return $this->map[$key];
    }

    public function has(string $key) : bool {
        return isset($this->map[$key]);
    }

}