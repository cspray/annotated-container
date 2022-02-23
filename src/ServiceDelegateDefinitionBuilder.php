<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;

class ServiceDelegateDefinitionBuilder {

    private ServiceDefinition $service;
    private string $delegateType;
    private string $delegateMethod;

    private function __construct() {}

    public static function forService(ServiceDefinition $service) : self {
        $instance = new self;
        $instance->service = $service;
        return $instance;
    }

    /**
     * @throws DefinitionBuilderException
     */
    public function withDelegateMethod(string $delegateType, string $delegateMethod) : self {
        if (empty($delegateType)) {
            throw new DefinitionBuilderException('The delegate type for a ServiceDelegateDefinition must not be blank.');
        }
        if (empty($delegateMethod)) {
            throw new DefinitionBuilderException('The delegate method for a ServiceDelegateDefinition must not be blank.');
        }
        $instance = clone $this;
        $instance->delegateType = $delegateType;
        $instance->delegateMethod = $delegateMethod;
        return $instance;
    }

    public function build() : ServiceDelegateDefinition {
        return new class($this->service, $this->delegateType, $this->delegateMethod) implements ServiceDelegateDefinition {

            private ServiceDefinition $serviceDefinition;
            private string $delegateType;
            private string $delegateMethod;

            public function __construct(ServiceDefinition $serviceDefinition, string $delegateType, string $delegateMethod) {
                $this->serviceDefinition = $serviceDefinition;
                $this->delegateType = $delegateType;
                $this->delegateMethod = $delegateMethod;
            }

            public function getDelegateType(): string {
                return $this->delegateType;
            }

            public function getDelegateMethod(): string {
                return $this->delegateMethod;
            }

            public function getServiceType(): ServiceDefinition {
                return $this->serviceDefinition;
            }
        };
    }

}