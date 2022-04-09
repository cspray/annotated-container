<?php

namespace Cspray\AnnotatedContainer\Internal;

use ArrayIterator;
use Cspray\AnnotatedContainer\AnnotationValue;
use Cspray\AnnotatedContainer\CollectionAnnotationValue;
use Traversable;

final class ArrayAnnotationValue implements CollectionAnnotationValue {

    /**
     * @var AnnotationValue[]
     */
    private array $items = [];

    public function __construct(array $items) {
        foreach ($items as $key => $item) {
            if (is_array($item)) {
                $this->items[$key] = new ArrayAnnotationValue($item);
            } else if ($item instanceof AnnotationValue) {
                $this->items[$key] = $item;
            } else {
                $this->items[$key] = new SingleAnnotationValue($item);
            }
        }
    }

    public function __serialize() : array {
        $items = [];
        foreach ($this->items as $key => $item) {
            $items[$key] = serialize($item);
        }
        return $items;
    }

    public function __unserialize(array $data): void {
        foreach ($data as $key => $serializedValue) {
            $this->items[$key] = unserialize($serializedValue);
        }
    }

    public function getCompileValue() : array {
        $values = [];
        foreach ($this as $key => $item) {
            $values[$key] = $item->getCompileValue();
        }
        return $values;
    }

    public function getRuntimeValue() : array {
        $values = [];
        foreach ($this as $key => $item) {
            $values[$key] = $item->getRunTimeValue();
        }
        return $values;
    }

    public function getIterator() : Traversable {
        return new ArrayIterator($this->items);
    }
}