<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;
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

    /**
     * @throws DefinitionBuilderException
     */
    public function withDelegateMethod(ObjectType $delegateType, string $delegateMethod) : self {
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