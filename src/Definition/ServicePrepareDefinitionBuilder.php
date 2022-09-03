<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Exception\InvalidServicePrepareDefinition;
use Cspray\Typiphy\ObjectType;

final class ServicePrepareDefinitionBuilder {

    private ObjectType $service;
    private string $method;

    private function __construct() {}

    public static function forMethod(ObjectType $serviceDefinition, string $method) : self {
        if (empty($method)) {
            throw InvalidServicePrepareDefinition::fromEmptyPrepareMethod();
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

            public function getService() : ObjectType {
                return $this->service;
            }

            public function getMethod() : string {
                return $this->method;
            }

            public function getAttribute() : ?object {
                // TODO: Implement getAttribute() method.
            }
        };
    }

}