<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event;

enum EventName {

    case BeforeBootstrap;

    case BeforeContainerAnalysis;

    case AddedServiceDefinition;

    case AddedServicePrepareDefinition;

    case AddedServiceDelegateDefinition;

    case AddedInjectDefinition;

    case AfterContainerAnalysis;

    case BeforeContainerCreation;

    case ServiceFilteredDueToProfiles;

    case ServiceShared;

    case InjectingMethodParameter;

    case InjectingProperty;

    case ServiceDelegated;

    case ServicePrepared;

    case ServiceAliasResolution;

    case AfterContainerCreation;

    case AfterBootstrap;


}