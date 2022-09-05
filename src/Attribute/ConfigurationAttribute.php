<?php

namespace Cspray\AnnotatedContainer\Attribute;

/**
 * Can be implemented by your own classes to act as an Attribute for defining a Configuration.
 *
 * The class that implements this interface should also be marked as an Attribute; it should be non-repeatable and only
 * able to target the class construct.
 */
interface ConfigurationAttribute {

    /**
     * Return an arbitrary name that can be used to retrieve the Configuration in addition to retrieval by the
     * Configuration's type.
     *
     * @return string|null
     */
    public function getName() : ?string;

}