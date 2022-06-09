<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\Typiphy\ObjectType;
use ReflectionClass;

function service(ContainerDefinitionBuilderContext $context, ObjectType $type, ?string $name = null, array $profiles = [], bool $isPrimary = false, bool $isShared = true) : ServiceDefinition {
    $reflection = new ReflectionClass($type->getName());
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

    if (empty($profiles)) {
        $profiles[] = 'default';
    }
    $serviceDefinitionBuilder = $serviceDefinitionBuilder->withProfiles($profiles);

    if ($isShared) {
        $serviceDefinitionBuilder = $serviceDefinitionBuilder->withShared();
    } else {
        $serviceDefinitionBuilder = $serviceDefinitionBuilder->withNotShared();
    }

    $serviceDefinition = $serviceDefinitionBuilder->build();
    $context->setBuilder($context->getBuilder()->withServiceDefinition($serviceDefinition));
    return $serviceDefinition;
}

function alias(ContainerDefinitionBuilderContext $context, ObjectType $abstract, ObjectType $concrete) : AliasDefinition {
    $aliasDefinition = AliasDefinitionBuilder::forAbstract($abstract)->withConcrete($concrete)->build();
    $context->setBuilder($context->getBuilder()->withAliasDefinition($aliasDefinition));
    return $aliasDefinition;
}

function serviceDelegate(ContainerDefinitionBuilderContext $context, ObjectType $service, ObjectType $factoryClass, string $factoryMethod) : ServiceDelegateDefinition {
    $serviceDelegateDefinition = ServiceDelegateDefinitionBuilder::forService($service)->withDelegateMethod($factoryClass, $factoryMethod)->build();
    $context->setBuilder($context->getBuilder()->withServiceDelegateDefinition($serviceDelegateDefinition));
    return $serviceDelegateDefinition;
}

function servicePrepare(ContainerDefinitionBuilderContext $context, ObjectType $serviceDefinition, string $method) : ServicePrepareDefinition {
    $servicePrepareDefinition = ServicePrepareDefinitionBuilder::forMethod($serviceDefinition, $method)->build();
    $context->setBuilder($context->getBuilder()->withServicePrepareDefinition($servicePrepareDefinition));
    return $servicePrepareDefinition;
}
