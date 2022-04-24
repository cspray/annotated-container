<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainer;

use Cspray\Typiphy\ObjectType;

/**
 * Defines a method that should be invoked when the given type has been created by the Injector.
 *
 * @package Cspray\AnnotatedContainer
 */
interface ServicePrepareDefinition {

    /**
     * The Service that requires some method to be invoked on it after it is instantiated.
     *
     * @return ObjectType
     */
    public function getService() : ObjectType;

    /**
     * The method that should be invoked on the Service.
     *
     * @return string
     */
    public function getMethod() : string;

}