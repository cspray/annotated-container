<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\InvalidAliasException;
use Cspray\Typiphy\ObjectType;

final class StandardAliasDefinitionResolver implements AliasDefinitionResolver {

    public function resolveAlias(
        ContainerDefinition $containerDefinition,
        ObjectType $abstractService
    ) : AliasDefinitionResolution {
        if ($this->isServiceDelegate($containerDefinition, $abstractService)) {
            $definition = null;
            $reason = AliasResolutionReason::ServiceIsDelegated;
        } else {
            $aliases = [];
            foreach ($containerDefinition->getAliasDefinitions() as $aliasDefinition) {
                if ($aliasDefinition->getAbstractService()->getName() === $abstractService->getName()) {
                    $aliases[] = $aliasDefinition;
                }
            }

            if (count($aliases) === 1) {
                $definition = $aliases[0];
                $reason = AliasResolutionReason::SingleConcreteService;
            } else if (count($aliases) > 1) {
                $definition = null;
                $primaryAliases = [];
                foreach ($aliases as $alias) {
                    $concreteDefinition = $this->getServiceDefinition($containerDefinition, $alias->getConcreteService());
                    if ($concreteDefinition?->isPrimary()) {
                        $primaryAliases[] = $alias;
                    }
                }

                if (count($primaryAliases) === 1) {
                    $definition = $primaryAliases[0];
                    $reason = AliasResolutionReason::ConcreteServiceIsPrimary;
                } else if (count($primaryAliases) === 0) {
                    $reason = AliasResolutionReason::MultipleConcreteService;
                } else {
                    $reason = AliasResolutionReason::MultiplePrimaryService;
                }
            } else {
                $definition = null;
                $reason = AliasResolutionReason::NoConcreteService;
            }
        }

        return new class($reason, $definition) implements AliasDefinitionResolution {

            public function __construct(
                private readonly AliasResolutionReason $reason,
                private readonly ?AliasDefinition $definition
            ) {}

            public function getAliasResolutionReason() : AliasResolutionReason {
                return $this->reason;
            }

            public function getAliasDefinition() : ?AliasDefinition {
                return $this->definition;
            }
        };
    }

    private function getServiceDefinition(ContainerDefinition $containerDefinition, ObjectType $objectType) : ?ServiceDefinition {
        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->getType()->getName() === $objectType->getName()) {
                return $serviceDefinition;
            }
        }

        return null;
    }

    private function isServiceDelegate(ContainerDefinition $containerDefinition, ObjectType $service) : bool {
        foreach ($containerDefinition->getServiceDelegateDefinitions() as $serviceDelegateDefinition) {
            if ($serviceDelegateDefinition->getServiceType()->getName() === $service->getName()) {
                return true;
            }
        }

        return false;
    }
}