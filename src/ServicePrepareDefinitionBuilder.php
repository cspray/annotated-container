<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;
use Cspray\Typiphy\ObjectType;

class ServicePrepareDefinitionBuilder {

    private ObjectType $service;
    private string $method;

    private function __construct() {}

    /**
     * Exception is thrown if the method passed is blank.
     *
     * @throws DefinitionBuilderException
     */
    public static function forMethod(ObjectType $serviceDefinition, string $method) : self {
        if (empty($method)) {
            throw new DefinitionBuilderException('A method for a ServicePrepareDefinition must not be blank.');
        }
        $instance = new self;
        $instance->service = $serviceDefinition;
        $instance->method = $method;
        return $instance;
    }

    public function build() : ServicePrepareDefinition {
        return new class($this->service, $this->method) implements ServicePrepareDefinition {

            public function __construct(
                private readonly ObjectType $service,
                private readonly string $method) {
            }

            public function getService(): ObjectType {
                return $this->service;
            }

            public function getMethod(): string {
                return $this->method;
            }
        };
    }

}