<?php

namespace Cspray\AnnotatedContainer;

use IteratorAggregate;

/**
 *
 */
interface CollectionAnnotationValue extends AnnotationValue, IteratorAggregate {

    public function getCompileValue() : array;

    public function getRuntimeValue() : array;

}