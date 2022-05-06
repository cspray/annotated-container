<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Implementations represents a specific place in parsed code where an annotated-container Attribute was discovered.
 *
 * This instance allows for linking the precise Attribute to its target reflection. Additionally, provides a means
 * to retrieve the instance for that particular Attribute.
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
     * @return ReflectionAttribute
     */
    public function getAttributeReflection() : ReflectionAttribute;

    /**
     * The Attribute instance retrieved from the given Attribute Reflection.
     *
     * @return Service|ServicePrepare|ServiceDelegate|Inject|Configuration
     */
    public function getAttributeInstance() : Service|ServicePrepare|ServiceDelegate|Inject|Configuration;

}