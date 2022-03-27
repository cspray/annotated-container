<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\ContainerDefinitionMergeException;

/**
 * The preferred method for constructing ContainerDefinition instances.
 */
final class ContainerDefinitionBuilder {

    /**
     * @var ServiceDefinition[]
     */
    private array $serviceDefinitions = [];
    private array $aliasDefinitions = [];
    private array $servicePrepareDefinitions = [];
    private array $injectScalarDefinitions = [];
    private array $injectServiceDefinitions = [];
    private array $serviceDelegateDefinitions = [];

    private function __construct() {}

    /**
     * @return static
     */
    public static function newDefinition() : self {
        return new self;
    }

    public function getServiceDefinition(string $type) : ?ServiceDefinition {
        foreach ($this->serviceDefinitions as $serviceDefinition) {
            if ($serviceDefinition->getType() === $type) {
                return $serviceDefinition;
            }
        }

        return null;
    }

    public function withServiceDefinition(ServiceDefinition $serviceDefinition) : self {
        $instance = clone $this;
        $instance->serviceDefinitions[] = $serviceDefinition;
        return $instance;
    }

    public function withAliasDefinition(AliasDefinition $aliasDefinition) : self {
        $instance = clone $this;
        $instance->aliasDefinitions[] = $aliasDefinition;
        return $instance;
    }

    public function withServicePrepareDefinition(ServicePrepareDefinition $servicePrepareDefinition) : self {
        $instance = clone $this;
        $instance->servicePrepareDefinitions[] = $servicePrepareDefinition;
        return $instance;
    }

    public function withInjectScalarDefinition(InjectScalarDefinition $injectScalarDefinition) : self {
        $instance = clone $this;
        $instance->injectScalarDefinitions[] = $injectScalarDefinition;
        return $instance;
    }

    public function withInjectServiceDefinition(InjectServiceDefinition $injectServiceDefinition) : self {
        $instance = clone $this;
        $instance->injectServiceDefinitions[] = $injectServiceDefinition;
        return $instance;
    }

    public function withServiceDelegateDefinition(ServiceDelegateDefinition $serviceDelegateDefinition) : self {
        $instance = clone $this;
        $instance->serviceDelegateDefinitions[] = $serviceDelegateDefinition;
        return $instance;
    }

    public function build() : ContainerDefinition {
        return new class(
            $this->serviceDefinitions,
            $this->aliasDefinitions,
            $this->servicePrepareDefinitions,
            $this->injectScalarDefinitions,
            $this->injectServiceDefinitions,
            $this->serviceDelegateDefinitions
        ) implements ContainerDefinition {

            public function __construct(
                private array $serviceDefinitions,
                private array $aliasDefinitions,
                private array $servicePrepareDefinitions,
                private array $injectScalarDefinitions,
                private array $injectServiceDefinitions,
                private array $serviceDelegateDefinitions
            ) {}

            public function merge(ContainerDefinition $containerDefinition) : ContainerDefinition {
                $merged = clone $this;
                foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
                    if ($this->hasServiceDefinition($serviceDefinition)) {
                        throw new ContainerDefinitionMergeException(sprintf(
                            'The ContainerDefinition already has a ServiceDefinition for %s.',
                            $serviceDefinition->getType()
                        ));
                    }
                    $merged->serviceDefinitions[] = $serviceDefinition;
                }

                foreach ($containerDefinition->getAliasDefinitions() as $aliasDefinition) {
                    if ($this->hasAliasDefinition($aliasDefinition)) {
                        throw new ContainerDefinitionMergeException(sprintf(
                            'The ContainerDefinition already has an AliasDefinition for %s aliased to %s.',
                            $aliasDefinition->getAbstractService()->getType(),
                            $aliasDefinition->getConcreteService()->getType()
                        ));
                    }
                    $merged->aliasDefinitions[] = $aliasDefinition;
                }

                foreach ($containerDefinition->getServicePrepareDefinitions() as $servicePrepareDefinition) {
                    $merged->servicePrepareDefinitions[] = $servicePrepareDefinition;
                }

                foreach ($containerDefinition->getInjectScalarDefinitions() as $injectScalarDefinition) {
                    $merged->injectScalarDefinitions[] = $injectScalarDefinition;
                }

                foreach ($containerDefinition->getInjectServiceDefinitions() as $injectServiceDefinition) {
                    $merged->injectServiceDefinitions[] = $injectServiceDefinition;
                }

                foreach ($containerDefinition->getServiceDelegateDefinitions() as $serviceDelegateDefinition) {
                    $merged->serviceDelegateDefinitions[] = $serviceDelegateDefinition;
                }

                return $merged;
            }

            public function getServiceDefinitions(): array {
                return $this->serviceDefinitions;
            }

            public function getAliasDefinitions(): array {
                return $this->aliasDefinitions;
            }

            public function getServicePrepareDefinitions(): array {
                return $this->servicePrepareDefinitions;
            }

            public function getInjectScalarDefinitions(): array {
                return $this->injectScalarDefinitions;
            }

            public function getInjectServiceDefinitions(): array {
                return $this->injectServiceDefinitions;
            }

            public function getServiceDelegateDefinitions(): array {
                return $this->serviceDelegateDefinitions;
            }

            private function hasServiceDefinition(ServiceDefinition $serviceDefinition) : bool {
                foreach ($this->serviceDefinitions as $storedDefinition) {
                    if ($serviceDefinition->equals($storedDefinition)) {
                        return true;
                    }
                }

                return false;
            }

            private function hasAliasDefinition(AliasDefinition $aliasDefinition) : bool {
                foreach ($this->aliasDefinitions as $storedDefinition) {
                    if ($aliasDefinition->equals($storedDefinition)) {
                        return true;
                    }
                }

                return false;
            }
        };
    }

}