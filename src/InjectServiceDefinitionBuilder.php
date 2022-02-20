<?php

namespace Cspray\AnnotatedContainer;

class InjectServiceDefinitionBuilder {

    private ServiceDefinition $service;
    private string $method;
    private string $paramType;
    private string $paramName;
    private ServiceDefinition $injectedService;

    private function __construct() {}

    public static function forMethod(ServiceDefinition $serviceDefinition, string $method) : self {
        if (empty($method)) {
            throw new \InvalidArgumentException('The method for an InjectServiceDefinition must not be blank.');
        }
        $instance = new self;
        $instance->service = $serviceDefinition;
        $instance->method = $method;
        return $instance;
    }

    public function withParam(string $type, string $name) : self {
        if (empty($type)) {
            throw new \InvalidArgumentException('The param type for an InjectServiceDefinition must not be blank.');
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('The param name for an InjectServiceDefinition must not be blank.');
        }
        $instance = clone $this;
        $instance->paramType = $type;
        $instance->paramName = $name;
        return $instance;
    }

    public function withInjectedService(ServiceDefinition $serviceDefinition) : self {
        $instance = clone $this;
        $instance->injectedService = $serviceDefinition;
        return $instance;
    }

    public function build() : InjectServiceDefinition {
        if (!isset($this->paramType)) {
            throw new \InvalidArgumentException('An InjectServiceDefinitionBuilder must have a parameter defined before building.');
        }
        if (!isset($this->injectedService)) {
            throw new \InvalidArgumentException('An InjectServiceDefinitionBuilder must have an injected service defined before building.');
        }
        return new class($this->service, $this->method, $this->paramType, $this->paramName, $this->injectedService) implements InjectServiceDefinition {

            private ServiceDefinition $service;
            private string $method;
            private string $paramType;
            private string $paramName;
            private ServiceDefinition $injectedService;

            public function __construct(
                ServiceDefinition $serviceDefinition,
                string $method,
                string $paramType,
                string $paramName,
                ServiceDefinition $injectedService
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

            public function getParamType(): string {
                return $this->paramType;
            }

            public function getInjectedService(): ServiceDefinition {
                return $this->injectedService;
            }
        };
    }

}