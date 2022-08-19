<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Exception\InvalidServiceDelegateDefinition;
use Cspray\Typiphy\ObjectType;

final class ServiceDelegateDefinitionBuilder {

    private ObjectType $service;
    private ObjectType $delegateType;
    private string $delegateMethod;

    private function __construct() {}

    public static function forService(ObjectType $service) : self {
        $instance = new self;
        $instance->service = $service;
        return $instance;
    }

    public function withDelegateMethod(ObjectType $delegateType, string $delegateMethod) : self {
        if (trim($delegateMethod) === '') {
            throw InvalidServiceDelegateDefinition::fromEmptyDelegateMethod();
        }
        $instance = clone $this;
        $instance->delegateType = $delegateType;
        $instance->delegateMethod = $delegateMethod;
        return $instance;
    }

    public function build() : ServiceDelegateDefinition {
        return new class($this->service, $this->delegateType, $this->delegateMethod) implements ServiceDelegateDefinition {

            private ObjectType $serviceDefinition;
            private ObjectType $delegateType;
            private string $delegateMethod;

            public function __construct(ObjectType $serviceDefinition, ObjectType $delegateType, string $delegateMethod) {
                $this->serviceDefinition = $serviceDefinition;
                $this->delegateType = $delegateType;
                $this->delegateMethod = $delegateMethod;
            }

            public function getDelegateType() : ObjectType {
                return $this->delegateType;
            }

            public function getDelegateMethod() : string {
                return $this->delegateMethod;
            }

            public function getServiceType() : ObjectType {
                return $this->serviceDefinition;
            }
        };
    }

}