<?php

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolution;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use Cspray\Typiphy\ObjectType;

final class YIiDiContainerFactory extends AbstractContainerFactory implements ContainerFactory {
    protected function getBackingContainerType(): ObjectType
    {
        // TODO: Implement getBackingContainerType() method.
    }

    protected function getContainerFactoryState(): ContainerFactoryState
    {
        // TODO: Implement getContainerFactoryState() method.
    }

    protected function handleServiceDefinition(ContainerFactoryState $state, ServiceDefinition $definition): void
    {
        // TODO: Implement handleServiceDefinition() method.
    }

    protected function handleAliasDefinition(ContainerFactoryState $state, AliasDefinitionResolution $resolution): void
    {
        // TODO: Implement handleAliasDefinition() method.
    }

    protected function handleServiceDelegateDefinition(ContainerFactoryState $state, ServiceDelegateDefinition $definition): void
    {
        // TODO: Implement handleServiceDelegateDefinition() method.
    }

    protected function handleServicePrepareDefinition(ContainerFactoryState $state, ServicePrepareDefinition $definition): void
    {
        // TODO: Implement handleServicePrepareDefinition() method.
    }

    protected function handleInjectDefinition(ContainerFactoryState $state, InjectDefinition $definition): void
    {
        // TODO: Implement handleInjectDefinition() method.
    }

    protected function handleConfigurationDefinition(ContainerFactoryState $state, ConfigurationDefinition $definition): void
    {
        // TODO: Implement handleConfigurationDefinition() method.
    }

    protected function createAnnotatedContainer(ContainerFactoryState $state, ActiveProfiles $activeProfiles): AnnotatedContainer
    {
        // TODO: Implement createAnnotatedContainer() method.
    }
}
