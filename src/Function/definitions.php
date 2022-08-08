<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;
use ReflectionClass;

function service(ContainerDefinitionBuilderContext $context, ObjectType $type, ?string $name = null, array $profiles = [], bool $isPrimary = false) : ServiceDefinition {
    /** @psalm-var class-string $typeName */
    $typeName = $type->getName();
    $reflection = new ReflectionClass($typeName);
    $methodArgs = [$type];
    $method = $reflection->isAbstract() || $reflection->isInterface() ? 'forAbstract' : 'forConcrete';
    /** @var ServiceDefinitionBuilder $serviceDefinitionBuilder */
    if ($method === 'forConcrete') {
        $methodArgs[] = $isPrimary;
    }
    /** @var ServiceDefinitionBuilder $serviceDefinitionBuilder */
    $serviceDefinitionBuilder = ServiceDefinitionBuilder::$method(...$methodArgs);
    if (isset($name)) {
        $serviceDefinitionBuilder = $serviceDefinitionBuilder->withName($name);
    }

    if (empty($profiles)) {
        $profiles[] = 'default';
    }
    $serviceDefinitionBuilder = $serviceDefinitionBuilder->withProfiles($profiles);

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

function injectMethodParam(ContainerDefinitionBuilderContext $context, ObjectType $service, string $method, string $paramName, Type|TypeUnion|TypeIntersect $type, mixed $value, array $profiles = [], string $from = null) : InjectDefinition {
    $injectDefinitionBuilder = InjectDefinitionBuilder::forService($service)
        ->withMethod($method, $type, $paramName)
        ->withValue($value);

    if (!empty($profiles)) {
        $injectDefinitionBuilder = $injectDefinitionBuilder->withProfiles(...$profiles);
    }

    if (isset($from)) {
        $injectDefinitionBuilder = $injectDefinitionBuilder->withStore($from);
    }

    $injectDefinition = $injectDefinitionBuilder->build();
    $context->setBuilder($context->getBuilder()->withInjectDefinition($injectDefinition));
    return $injectDefinition;
}

function injectProperty(ContainerDefinitionBuilderContext $context, ObjectType $service, string $property, Type|TypeUnion|TypeIntersect $type, mixed $value, array $profiles = [], string $from = null) : InjectDefinition {
    $injectDefinitionBuilder = InjectDefinitionBuilder::forService($service)
        ->withProperty($type, $property)
        ->withValue($value);

    if (!empty($profiles)) {
        $injectDefinitionBuilder = $injectDefinitionBuilder->withProfiles(...$profiles);
    }

    if (isset($from)) {
        $injectDefinitionBuilder = $injectDefinitionBuilder->withStore($from);
    }

    $injectDefinition = $injectDefinitionBuilder->build();
    $context->setBuilder($context->getBuilder()->withInjectDefinition($injectDefinition));
    return $injectDefinition;
}
