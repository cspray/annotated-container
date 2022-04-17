<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Internal\ConstantAnnotationValue;
use Cspray\AnnotatedContainer\Internal\EnvironmentAnnotationValue;
use Cspray\AnnotatedContainer\Internal\ArrayAnnotationValue;
use Cspray\AnnotatedContainer\Internal\SingleAnnotationValue;
use ReflectionClass;

function arrayValue(array $items) : CollectionAnnotationValue {
    return new ArrayAnnotationValue($items);
}

function scalarValue(string|int|float|bool $value) : AnnotationValue {
    return new SingleAnnotationValue($value);
}

function constantValue(string $constant) : AnnotationValue {
    return new ConstantAnnotationValue($constant);
}

function envValue(string $envVar) : AnnotationValue {
    return new EnvironmentAnnotationValue($envVar);
}

function service(ContainerDefinitionBuilderContext $context, string $type, ?AnnotationValue $name = null, CollectionAnnotationValue $profiles = null, bool $isPrimary = false, bool $isShared = true) : ServiceDefinition {
    $reflection = new ReflectionClass($type);
    $methodArgs = [$type];
    $method = $reflection->isAbstract() || $reflection->isInterface() ? 'forAbstract' : 'forConcrete';
    /** @var ServiceDefinitionBuilder $serviceDefinitionBuilder */
    if ($method === 'forConcrete') {
        $methodArgs[] = $isPrimary;
    }
    $serviceDefinitionBuilder = ServiceDefinitionBuilder::$method(...$methodArgs);
    if (isset($name)) {
        $serviceDefinitionBuilder = $serviceDefinitionBuilder->withName($name);
    }

    if (isset($profiles)) {
        $serviceDefinitionBuilder = $serviceDefinitionBuilder->withProfiles($profiles);
    }

    if ($isShared) {
        $serviceDefinitionBuilder = $serviceDefinitionBuilder->withShared();
    } else {
        $serviceDefinitionBuilder = $serviceDefinitionBuilder->withNotShared();
    }

    $serviceDefinition = $serviceDefinitionBuilder->build();
    $context->setBuilder($context->getBuilder()->withServiceDefinition($serviceDefinition));
    return $serviceDefinition;
}

function alias(ContainerDefinitionBuilderContext $context, ServiceDefinition $abstract, ServiceDefinition $concrete) : AliasDefinition {
    $aliasDefinition = AliasDefinitionBuilder::forAbstract($abstract)->withConcrete($concrete)->build();
    $context->setBuilder($context->getBuilder()->withAliasDefinition($aliasDefinition));
    return $aliasDefinition;
}

function serviceDelegate(ContainerDefinitionBuilderContext $context, ServiceDefinition $service, string $factoryClass, string $factoryMethod) : ServiceDelegateDefinition {
    $serviceDelegateDefinition = ServiceDelegateDefinitionBuilder::forService($service)->withDelegateMethod($factoryClass, $factoryMethod)->build();
    $context->setBuilder($context->getBuilder()->withServiceDelegateDefinition($serviceDelegateDefinition));
    return $serviceDelegateDefinition;
}

function servicePrepare(ContainerDefinitionBuilderContext $context, ServiceDefinition $serviceDefinition, string $method) : ServicePrepareDefinition {
    $servicePrepareDefinition = ServicePrepareDefinitionBuilder::forMethod($serviceDefinition, $method)->build();
    $context->setBuilder($context->getBuilder()->withServicePrepareDefinition($servicePrepareDefinition));
    return $servicePrepareDefinition;
}

function injectScalar(ContainerDefinitionBuilderContext $context, ServiceDefinition $type, string $method, string $paramName, ScalarType $paramType, AnnotationValue $value, CollectionAnnotationValue $profiles = null) : InjectScalarDefinition {
    if (!isset($profiles)) {
        $profiles = arrayValue([]);
    }
    $injectScalarDefinition = InjectScalarDefinitionBuilder::forMethod($type, $method)
        ->withParam($paramType, $paramName)
        ->withProfiles($profiles)
        ->withValue($value)
        ->build();
    $context->setBuilder($context->getBuilder()->withInjectScalarDefinition($injectScalarDefinition));
    return $injectScalarDefinition;
}

function injectEnv(ContainerDefinitionBuilderContext $context, ServiceDefinition $type, string $method, string $paramName, ScalarType $paramType, string $varName, CollectionAnnotationValue $profiles = null) : InjectScalarDefinition {
    if (!isset($profiles)) {
        $profiles = arrayValue([]);
    }
    $injectScalarDefinition = InjectScalarDefinitionBuilder::forMethod($type, $method)
        ->withParam($paramType, $paramName)
        ->withValue(envValue($varName))
        ->withProfiles($profiles)
        ->build();
    $context->setBuilder($context->getBuilder()->withInjectScalarDefinition($injectScalarDefinition));
    return $injectScalarDefinition;
}

function injectService(ContainerDefinitionBuilderContext $context, ServiceDefinition $type, string $method, string $paramName, string $paramType, AnnotationValue $injectType) : InjectServiceDefinition {
    $injectServiceDefinition = InjectServiceDefinitionBuilder::forMethod($type, $method)
        ->withParam($paramType, $paramName)
        ->withInjectedService($injectType)
        ->build();
    $context->setBuilder($context->getBuilder()->withInjectServiceDefinition($injectServiceDefinition));
    return $injectServiceDefinition;
}