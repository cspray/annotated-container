<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

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
    private array $injectDefinitions = [];
    private array $serviceDelegateDefinitions = [];
    private array $configurationDefinitions = [];

    private function __construct() {}

    /**
     * @return static
     */
    public static function newDefinition() : self {
        return new self;
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

    public function withServiceDelegateDefinition(ServiceDelegateDefinition $serviceDelegateDefinition) : self {
        $instance = clone $this;
        $instance->serviceDelegateDefinitions[] = $serviceDelegateDefinition;
        return $instance;
    }

    public function withInjectDefinition(InjectDefinition $injectDefinition) : self {
        $instance = clone $this;
        $instance->injectDefinitions[] = $injectDefinition;
        return $instance;
    }

    public function withConfigurationDefinition(ConfigurationDefinition $configurationDefinition) : self {
        $instance = clone $this;
        $instance->configurationDefinitions[] = $configurationDefinition;
        return $instance;
    }

    public function getServiceDefinitions() : array {
        return $this->serviceDefinitions;
    }

    public function build() : ContainerDefinition {
        return new class(
            $this->serviceDefinitions,
            $this->aliasDefinitions,
            $this->servicePrepareDefinitions,
            $this->injectDefinitions,
            $this->serviceDelegateDefinitions,
            $this->configurationDefinitions
        ) implements ContainerDefinition {

            public function __construct(
                private array $serviceDefinitions,
                private array $aliasDefinitions,
                private array $servicePrepareDefinitions,
                private array $injectDefinitions,
                private array $serviceDelegateDefinitions,
                private array $configurationDefinitions
            ) {}

            public function merge(ContainerDefinition $containerDefinition) : ContainerDefinition {
                $merged = clone $this;
                foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
                    if ($this->hasServiceDefinition($serviceDefinition)) {
                        throw new ContainerDefinitionMergeException(sprintf(
                            'The ContainerDefinition already has a ServiceDefinition for %s.',
                            $serviceDefinition->getType()->getName()
                        ));
                    }
                    $merged->serviceDefinitions[] = $serviceDefinition;
                }

                foreach ($containerDefinition->getAliasDefinitions() as $aliasDefinition) {
                    if ($this->hasAliasDefinition($aliasDefinition)) {
                        throw new ContainerDefinitionMergeException(sprintf(
                            'The ContainerDefinition already has an AliasDefinition for %s aliased to %s.',
                            $aliasDefinition->getAbstractService()->getName(),
                            $aliasDefinition->getConcreteService()->getName()
                        ));
                    }
                    $merged->aliasDefinitions[] = $aliasDefinition;
                }

                foreach ($containerDefinition->getServicePrepareDefinitions() as $servicePrepareDefinition) {
                    $merged->servicePrepareDefinitions[] = $servicePrepareDefinition;
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

            public function getInjectDefinitions(): array {
                return $this->injectDefinitions;
            }

            public function getServiceDelegateDefinitions(): array {
                return $this->serviceDelegateDefinitions;
            }

            public function getConfigurationDefinitions() : array {
                return $this->configurationDefinitions;
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