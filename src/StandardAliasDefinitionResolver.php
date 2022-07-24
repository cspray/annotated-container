<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\InvalidAliasException;
use Cspray\Typiphy\ObjectType;

final class StandardAliasDefinitionResolver implements AliasDefinitionResolver {

    public function resolveAlias(
        ContainerDefinition $containerDefinition,
        ObjectType $abstractService
    ) : AliasDefinitionResolution {

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
            $reason = AliasResolutionReason::MultipleConcreteService;
            $primaryAlias = null;
            foreach ($aliases as $alias) {
                $concreteDefinition = $this->getServiceDefinition($containerDefinition, $alias->getConcreteService());
                if ($primaryAlias === null && $concreteDefinition?->isPrimary()) {
                    $primaryAlias = $alias;
                } else if ($primaryAlias !== null && $concreteDefinition?->isPrimary()) {
                    $primaryAlias = null;
                    break;
                }
            }

            if ($primaryAlias !== null) {
                $definition = $primaryAlias;
                $reason = AliasResolutionReason::ConcreteServiceIsPrimary;
            }
        } else {
            $definition = null;
            $reason = AliasResolutionReason::NoConcreteService;
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
}