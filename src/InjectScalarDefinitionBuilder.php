<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;

/**
 * The preferred method for constructing InjectScalarDefinition instances.
 */
final class InjectScalarDefinitionBuilder {

    private ServiceDefinition $serviceDefinition;
    private ?AnnotationValue $profiles = null;
    private string $method;
    private ScalarType $paramType;
    private string $paramName;
    private AnnotationValue $paramValue;

    private function __construct() {}

    /**
     * Start a new InjectScalarDefinition for the given Service's method.
     *
     * A DefinitionBuilderException will be thrown if the method passed is invalid.
     *
     * @param ServiceDefinition $service
     * @param string $method
     * @return static
     * @throws DefinitionBuilderException
     */
    public static function forMethod(ServiceDefinition $service, string $method) : self {
        if (empty($method)) {
            throw new DefinitionBuilderException('The method for an InjectScalarDefinition must not be blank.');
        }
        $instance = new self;
        $instance->serviceDefinition = $service;
        $instance->method = $method;
        return $instance;
    }

    /**
     * Define the parameter that should have a scalar value injected into it.
     *
     * @param ScalarType $paramType
     * @param string $name
     * @return $this
     * @throws DefinitionBuilderException
     */
    public function withParam(ScalarType $paramType, string $name) : self {
        if (empty($name)) {
            throw new DefinitionBuilderException('The param name for an InjectScalarDefinition must not be blank.');
        }
        $instance = clone $this;
        $instance->paramType = $paramType;
        $instance->paramName = $name;
        return $instance;
    }

    /**
     * Define the value that should be injected into the parameter value on method invocation.
     *
     * @param AnnotationValue $value
     * @return $this
     */
    public function withValue(AnnotationValue $value) : self {
        $instance = clone $this;
        $instance->paramValue = $value;
        return $instance;
    }

    public function withProfiles(AnnotationValue $profile) : self {
        $instance = clone $this;
        $instance->profiles = $profile;
        return $instance;
    }

    /**
     * Returns the built InjectScalarDefinition.
     *
     * If required data is not present a DefinitionBuilderException will be thrown.
     *
     * @return InjectScalarDefinition
     * @throws DefinitionBuilderException
     */
    public function build() : InjectScalarDefinition {
        if (!isset($this->paramName)) {
            throw new DefinitionBuilderException('An InjectScalarDefinitionBuilder must have a parameter defined before building.');
        }
        if (!isset($this->paramValue)) {
            throw new DefinitionBuilderException('An InjectScalarDefinitionBuilder must have a parameter value defined before building.');
        }

        $profiles = $this->profiles;
        if (is_null($profiles)) {
            $profiles = arrayValue(['default']);
        }
        return new class($this->serviceDefinition, $profiles, $this->method, $this->paramType, $this->paramName, $this->paramValue) implements InjectScalarDefinition {

            public function __construct(
                private ServiceDefinition $serviceDefinition,
                private AnnotationValue $profiles,
                private string $method,
                private ScalarType $paramType,
                private string $paramName,
                private AnnotationValue $value
            ) {}

            public function getService(): ServiceDefinition {
                return $this->serviceDefinition;
            }

            public function getMethod(): string {
                return $this->method;
            }

            public function getParamName(): string {
                return $this->paramName;
            }

            public function getParamType(): ScalarType {
                return $this->paramType;
            }

            public function getValue(): AnnotationValue {
                return $this->value;
            }

            public function getProfiles(): AnnotationValue {
                return $this->profiles;
            }
        };
    }

}