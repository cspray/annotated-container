<?php

use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectTargetIdentifier;
use Cspray\AnnotatedContainer\Definition\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinitionBuilder;

use Cspray\AnnotatedContainer\Event\AnnotatedContainerEmitter;
use Cspray\AnnotatedContainer\Event\AnnotatedContainerEvent;
use Cspray\AnnotatedContainer\Event\AnnotatedContainerLifecycle;
use Cspray\AnnotatedContainer\Event\AnnotatedContainerListener;
use Cspray\AnnotatedContainer\Event\ServiceGatheringListener;
use Cspray\AnnotatedContainer\Event\StandardAnnotatedContainerEmitter;


class_alias(AliasDefinition::class, 'Cspray\AnnotatedContainer\AliasDefinition');
class_alias(AliasDefinitionBuilder::class, 'Cspray\AnnotatedContainer\AliasDefinitionBuilder');
class_alias(ConfigurationDefinition::class, 'Cspray\AnnotatedContainer\ConfigurationDefinition');
class_alias(ConfigurationDefinitionBuilder::class, 'Cspray\AnnotatedContainer\ConfigurationDefinitionBuilder');
class_alias(ContainerDefinition::class, 'Cspray\AnnotatedContainer\ContainerDefinition');
class_alias(ContainerDefinitionBuilder::class, 'Cspray\AnnotatedContainer\ContainerDefinitionBuilder');
class_alias(InjectDefinition::class, 'Cspray\AnnotatedContainer\InjectDefinition');
class_alias(InjectDefinitionBuilder::class, 'Cspray\AnnotatedContainer\InjectDefinitionBuilder');
class_alias(InjectTargetIdentifier::class, 'Cspray\AnnotatedContainer\InjectTargetIdentifier');
class_alias(ProfilesAwareContainerDefinition::class, 'Cspray\AnnotatedContainer\ProfilesAwareContainerDefinition');
class_alias(ServiceDefinition::class, 'Cspray\AnnotatedContainer\ServiceDefinition');
class_alias(ServiceDefinitionBuilder::class, 'Cspray\AnnotatedContainer\ServiceDefinitionBuilder');
class_alias(ServiceDelegateDefinition::class, 'Cspray\AnnotatedContainer\ServiceDelegateDefinition');
class_alias(ServiceDelegateDefinitionBuilder::class, 'Cspray\AnnotatedContainer\ServiceDelegateDefinitionBuilder');
class_alias(ServicePrepareDefinition::class, 'Cspray\AnnotatedContainer\ServicePrepareDefinition');
class_alias(ServicePrepareDefinitionBuilder::class, 'Cspray\AnnotatedContainer\ServicePrepareDefinitionBuilder');

class_alias(AnnotatedContainerEmitter::class, 'Cspray\AnnotatedContainer\AnnotatedContainerEmitter');
class_alias(AnnotatedContainerEvent::class, 'Cspray\AnnotatedContainer\AnnotatedContainerEvent');
class_alias(AnnotatedContainerLifecycle::class, 'Cspray\AnnotatedContainer\AnnotatedContainerLifecycle');
class_alias(AnnotatedContainerListener::class, 'Cspray\AnnotatedContainer\AnnotatedContainerListener');
class_alias(ServiceGatheringListener::class, 'Cspray\AnnotatedContainer\ServiceGatheringListener');
class_alias(StandardAnnotatedContainerEmitter::class, 'Cspray\AnnotatedContainer\StandardAnnotatedContainerEmitter');