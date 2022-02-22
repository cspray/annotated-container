<?php

namespace Cspray\AnnotatedContainer;

final class AliasDefinitionBuilder {

    private ServiceDefinition $abstractType;
    private ServiceDefinition $concreteType;

    private function __construct() {}

    public static function forAbstract(ServiceDefinition $serviceDefinition) : self {
        if (!$serviceDefinition->isAbstract()) {
            throw new \InvalidArgumentException(sprintf(
                'Attempted to assign concrete type %s as an abstract alias.',
                $serviceDefinition->getType()
            ));
        }
        $instance = new self;
        $instance->abstractType = $serviceDefinition;
        return $instance;
    }

    public function withConcrete(ServiceDefinition $serviceDefinition) : self {
        if ($serviceDefinition->isAbstract()) {
            throw new \InvalidArgumentException(sprintf(
                'Attempted to assign abstract type %s as a concrete alias.',
                $serviceDefinition->getType()
            ));
        }
        $instance = clone $this;
        $instance->concreteType = $serviceDefinition;
        return $instance;
    }

    public function build() : AliasDefinition {
        return new class($this->abstractType, $this->concreteType) implements AliasDefinition {
            private ServiceDefinition $abstractService;
            private ServiceDefinition $concreteService;

            public function __construct(ServiceDefinition $abstractService, ServiceDefinition $concreteService) {
                $this->abstractService = $abstractService;
                $this->concreteService = $concreteService;
            }

            public function getAbstractService(): ServiceDefinition {
                return $this->abstractService;
            }

            public function getConcreteService(): ServiceDefinition {
                return $this->concreteService;
            }

            public function equals(AliasDefinition $aliasDefinition): bool {
                return $this->abstractService->equals($aliasDefinition->getAbstractService()) && $this->concreteService->equals($aliasDefinition->getConcreteService());
            }
        };
    }

}