<?php

namespace Cspray\AnnotatedContainer\Attribute;

/**
 * Can be implemented by your own classes to act as an Attribute for defining a class method as a Service factory.
 *
 * The class that implements this interface should also be marked as an Attribute; it should be not repeatable and able
 * to target only class methods.
 */
interface ServiceDelegateAttribute {

    /**
     * Return the Service this class method is responsible for creating, if null is returned the method's return type
     * will be used to determine the Service.
     *
     * @return string|null
     */
    public function getService() : ?string;

}