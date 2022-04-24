<?php

namespace Cspray\AnnotatedContainer;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

interface AnnotatedTarget {

    public function getTargetType() : AnnotatedTargetType;

    public function getTargetReflection() : ReflectionClass|ReflectionClassConstant|ReflectionProperty|ReflectionMethod|ReflectionParameter|ReflectionFunction;

    public function getAttributeReflection() : ReflectionAttribute;

    public function getAttributeInstance() : object;

}