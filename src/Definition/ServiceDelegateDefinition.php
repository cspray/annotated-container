<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\Typiphy\ObjectType;

/**
 * Defines a factory method for creating a specific type of Service.
 */
interface ServiceDelegateDefinition {

    /**
     * Return the FQCN for the factory class that should create this Service.
     *
     * Please note that you can specify other Services in the Container and have them injected into the constructor
     * of this factory class.
     *
     * @return ObjectType
     */
    public function getDelegateType() : ObjectType;

    /**
     * Return the method on the delegateType that should be invoked to create the Service.
     *
     * The method can accept Services or otherwise inject values from the Container. The Container will be used to
     * execute the factory method.
     *
     * @return string
     */
    public function getDelegateMethod() : string;

    public function getServiceType() : ObjectType;

    public function getAttribute() : ?object;

}