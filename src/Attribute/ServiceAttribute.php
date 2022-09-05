<?php

namespace Cspray\AnnotatedContainer\Attribute;

/**
 * Can be implemented by your own classes to act as an Attribute for defining your own Services wired into your
 * Container.
 *
 * The class that implements this interface should also be marked as an Attribute; it should not be repeatable and
 * should be able to only target the class construct.
 */
interface ServiceAttribute {

    /**
     * A list of profiles that have to be active for this Attribute to be wired into the Container.
     *
     * If an empty list is returned the Attribute will be implicitly assigned the 'default' profile.
     *
     * @return list<string>
     */
    public function getProfiles() : array;

    /**
     * Return whether this concrete service should be considered the primary service when resolving an abstract alias.
     *
     * @return bool
     */
    public function isPrimary() : bool;

    /**
     * Return a string to fetch a service by an arbitrary name in addition to its fully qualified type.
     *
     * @return string|null
     */
    public function getName() : ?string;

}
