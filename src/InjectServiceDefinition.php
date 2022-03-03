<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * Define a specific Service that should be injected into a Service method's parameter.
 */
interface InjectServiceDefinition {

    /**
     * The Service that depends on some other Service to be injected into a method.
     *
     * @return ServiceDefinition
     */
    public function getService() : ServiceDefinition;

    /**
     * The method that requires a specific Service be injected into it.
     *
     * @return string
     */
    public function getMethod() : string;

    /**
     * The name of the parameter that requires a specific Service be injected into it.
     *
     * @return string
     */
    public function getParamName() : string;

    /**
     * The fully-qualified-class-name for the Service type that should be injected.
     *
     * @return string
     */
    public function getParamType() : string;

    /**
     * The Service that should be injected into the given parameter.
     *
     * @return ServiceDefinition
     */
    public function getInjectedService() : ServiceDefinition;

}