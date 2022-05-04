<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

interface AnnotatedTarget {

    public function getTargetReflection() : ReflectionClass|ReflectionMethod|ReflectionParameter|ReflectionProperty;

    public function getAttributeReflection() : ReflectionAttribute;

    public function getAttributeInstance() : object;

}