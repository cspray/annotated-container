<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\AnnotationValue;
use Cspray\AnnotatedContainer\CollectionAnnotationValue;
use function Cspray\AnnotatedContainer\arrayValue;
use function Cspray\AnnotatedContainer\scalarValue;

/**
 * @Internal
 */
final class AnnotationArguments {

    private array $map = [];

    public function put(string $key, AnnotationValue $value) : void {
        $this->map[$key] = $value;
    }

    public function get(string $key, string|int|float|bool|array $default = null) : AnnotationValue|CollectionAnnotationValue {
        if (!$this->has($key)) {
            if (is_array($default)) {
                return arrayValue($default);
            } else if (!is_null($default)) {
                return scalarValue($default);
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