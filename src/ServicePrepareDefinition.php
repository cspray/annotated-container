<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainer;

/**
 * Defines a method that should be invoked when the given type has been created by the Injector.
 *
 * @package Cspray\AnnotatedContainer
 */
interface ServicePrepareDefinition {

    public function getService() : ServiceDefinition;

    public function getMethod() : string;

}