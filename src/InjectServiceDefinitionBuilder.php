<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;

/**
 * The preferred method for constructing InjectServiceDefinition instances.
 */
final class InjectServiceDefinitionBuilder {

    private ServiceDefinition $service;
    private string $method;
    private string $paramType;
    private string $paramName;
    private AnnotationValue $injectedService;

    private function __construct() {}

    /**
     * Define the method that requires a Service be injected into it.
     *
     * If the Service method is invalid a DefinitionBuilderException will be thrown.
     *
     * @param ServiceDefinition $serviceDefinition
     * @param string $method
     * @return static
     * @throws DefinitionBuilderException
     */
    public static function forMethod(ServiceDefinition $serviceDefinition, string $method) : self {
        if (empty($method)) {
            throw new DefinitionBuilderException('The method for an InjectServiceDefinition must not be blank.');
        }
        $instance = new self;
        $instance->service = $serviceDefinition;
        $instance->method = $method;
        return $instance;
    }

    /**
     * Define the FQCN type and name for the parameter that requires a Service be injected into it.
     *
     * @param string $type
     * @param string $name
     * @return $this
     * @throws DefinitionBuilderException
     */
    public function withParam(string $type, string $name) : self {
        if (empty($type)) {
            throw new DefinitionBuilderException('The param type for an InjectServiceDefinition must not be blank.');
        }
        if (empty($name)) {
            throw new DefinitionBuilderException('The param name for an InjectServiceDefinition must not be blank.');
        }
        $instance = clone $this;
        $instance->paramType = $type;
        $instance->paramName = $name;
        return $instance;
    }

    /**
     * Define the actual Service that will be injected into the defined parameter.
     *
     * @param AnnotationValue $annotationValue
     * @return $this
     */
    public function withInjectedService(AnnotationValue $annotationValue) : self {
        $instance = clone $this;
        $instance->injectedService = $annotationValue;
        return $instance;
    }

    /**
     * @return InjectServiceDefinition
     * @throws DefinitionBuilderException
     */
    public function build() : InjectServiceDefinition {
        if (!isset($this->paramType)) {
            throw new DefinitionBuilderException('An InjectServiceDefinitionBuilder must have a parameter defined before building.');
        }
        if (!isset($this->injectedService)) {
            throw new DefinitionBuilderException('An InjectServiceDefinitionBuilder must have an injected service defined before building.');
        }
        return new class($this->service, $this->method, $this->paramType, $this->paramName, $this->injectedService) implements InjectServiceDefinition {

            private ServiceDefinition $service;
            private string $method;
            private string $paramType;
            private string $paramName;
            private AnnotationValue $injectedService;

            public function __construct(
                ServiceDefinition $serviceDefinition,
                string $method,
                string $paramType,
                string $paramName,
                AnnotationValue $injectedService
            ) {
                $this->service = $serviceDefinition;
                $this->method = $method;
                $this->paramType = $paramType;
                $this->paramName = $paramName;
                $this->injectedService = $injectedService;
            }

            public function getService(): ServiceDefinition {
                return $this->service;
            }

            public function getMethod(): string {
                return $this->method;
            }

            public function getParamName(): string {
                return $this->paramName;
            }

            public function getParamType() : string {
                return $this->paramType;
            }

            public function getInjectedService(): AnnotationValue {
                return $this->injectedService;
            }
        };
    }

}