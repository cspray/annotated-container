<?php

namespace Cspray\AnnotatedContainer\Attribute;

/**
 * Can be implemented by your own classes to act as an Attribute for defining values that can be injected into service
 * method parameters or a Configuration property.
 *
 * The class that implements this interface should also be marked as an Attribute; it can be repeatable and should be
 * able to target a method parameter and a class property.
 */
interface InjectAttribute {

    /**
     * A serializable value that will be injected into the parameter or property, as appropriate for the target.
     *
     * @return mixed
     */
    public function getValue() : mixed;

    /**
     * A list of profiles that have to be active for this Attribute to be used.
     *
     * If an empty list is returned the Attribute will be implicitly assigned the 'default' profile.
     *
     * @return list<string>
     */
    public function getProfiles() : array;

    /**
     * If the actual value for this injection should come from a ParameterStore implementation then return its name,
     * otherwise return null.
     *
     * @return string|null
     */
    public function getFrom() : ?string;

}