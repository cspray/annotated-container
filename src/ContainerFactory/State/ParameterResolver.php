<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\State;

use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;

/**
 * @internal
 */
final class ParameterResolver {

    public function __construct(
        private readonly InjectParameterValueProvider $injectParameterValueProvider,
    ) {
    }

    public function resolveParametersForServiceConstructor(
        object $container,
        ContainerFactoryState $state,
        ServiceDefinition $serviceDefinition,
    ) : array {
        return $this->listOfInjectDefinitionsToArray(
            $container,
            $state,
            $state->constructorInjectDefinitionsForServiceDefinition($serviceDefinition)
        );
    }

    public function resolveParametersForServicePrepare(
        object $container,
        ContainerFactoryState $state,
        ServicePrepareDefinition $servicePrepareDefinition,
    ) : array {
        return $this->listOfInjectDefinitionsToArray(
            $container,
            $state,
            $state->injectDefinitionsForServicePrepareDefinition($servicePrepareDefinition)
        );
    }

    public function resolveParametersForServiceDelegate(
        object $container,
        ContainerFactoryState $state,
        ServiceDelegateDefinition $serviceDelegateDefinition,
    ) : array {
        return $this->listOfInjectDefinitionsToArray(
            $container,
            $state,
            $state->injectDefinitionsForServiceDelegateDefinition($serviceDelegateDefinition)
        );
    }

    /**
     * @param object $container
     * @param list<InjectDefinition> $definitions
     * @return array<non-empty-string, mixed>
     */
    private function listOfInjectDefinitionsToArray(object $container, ContainerFactoryState $state, array $definitions) : array {
        $params = [];
        foreach ($definitions as $injectDefinition) {
            $injectParameterValue = $this->injectParameterValueProvider->resolveInjectParameterValue(
                $container,
                $state,
                $injectDefinition
            );
            $params[$injectParameterValue->name] = $injectParameterValue->value;
        }

        return $params;
    }
}
