<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Implementations represents a specific place in parsed code where an annotated-container Attribute was discovered.
 *
 * This instance allows for the linking the precise Attribute to its target reflection. Additionally, provides a means
 * to retrieve the instance for that particular Attribute.
 *
 */
interface AnnotatedTarget {

    /**
     * The Reflector that represents the target that the Attribute was found on.
     *
     * These types are not meant to represent all possible Reflector interfaces that could be targeted by an Attribute.
     * Instead, these types are only those targets for Attributes that exist in annotated-container.
     *
     * @return ReflectionClass|ReflectionMethod|ReflectionParameter|ReflectionProperty
     */
    public function getTargetReflection() : ReflectionClass|ReflectionMethod|ReflectionParameter|ReflectionProperty;

    /**
     * The Reflector that represents the Attribute itself.
     *
     *
     *
     * @return ReflectionAttribute
     */
    public function getAttributeReflection() : ReflectionAttribute;

    public function getAttributeInstance() : object;

}