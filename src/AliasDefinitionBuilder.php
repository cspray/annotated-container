<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;

/**
 * The preferred method for constructing AliasDefinition instances.
 */
final class AliasDefinitionBuilder {

    private ServiceDefinition $abstractType;
    private ServiceDefinition $concreteType;

    private function __construct() {}

    /**
     * Define the abstract Service that should have an alias defined for it.
     *
     * @param ServiceDefinition $serviceDefinition
     * @return static
     * @throws DefinitionBuilderException
     */
    public static function forAbstract(ServiceDefinition $serviceDefinition) : self {
        if (!$serviceDefinition->isAbstract()) {
            throw new DefinitionBuilderException(sprintf(
                'Attempted to assign concrete type %s as an abstract alias.',
                $serviceDefinition->getType()
            ));
        }
        $instance = new self;
        $instance->abstractType = $serviceDefinition;
        return $instance;
    }

    /**
     * Define the concrete Service that acts as an alias for the given abstract Service.
     *
     * This method is immutable and a new AliasDefinitionBuilder will be returned.
     *
     * @param ServiceDefinition $serviceDefinition
     * @return $this
     * @throws DefinitionBuilderException
     */
    public function withConcrete(ServiceDefinition $serviceDefinition) : self {
        if ($serviceDefinition->isAbstract()) {
            throw new DefinitionBuilderException(sprintf(
                'Attempted to assign abstract type %s as a concrete alias.',
                $serviceDefinition->getType()
            ));
        }
        $instance = clone $this;
        $instance->concreteType = $serviceDefinition;
        return $instance;
    }

    /**
     * Returns an AliasDefinition with the provided abstract and concrete Services.
     *
     * @return AliasDefinition
     */
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