# Functional API

Annotated Container provides a series of functions that are designed to:

- Make bootstrapping easier
- Provide a mechanism for defining services that can't be annotated
- Define a standardized way for passing arguments to `AutowireableFactory` and `AutowireableInvoker`.

This document lists the functions for each purpose.

## Bootstrapping Functions

```php
\Cspray\AnnotatedContainer\containerFactory(SupportedContainers $supportedContainer = \Cspray\AnnotatedContainer\SupportedContainers::Default) : ContainerFactory;

\Cspray\AnnotatedContainer\compiler(string $cacheDir = null) : \Cspray\AnnotatedContainer\ContainerDefinitionCompiler;

\Cspray\AnnotatedContainer\eventEmitter() : \Cspray\AnnotatedContainer\AnnotatedContainerEmitter;
```

## Defining Services

```php
\Cspray\AnnotatedContainer\service(
    \Cspray\AnnotatedContainer\ContainerDefinitionBuilderContext $context,
    \Cspray\Typiphy\ObjectType $service,
    ?string $name = null,
    array $profiles = [],
    bool $isPrimary = false
) : \Cspray\AnnotatedContainer\ServiceDefinition;

\Cspray\AnnotatedContainer\alias(
    \Cspray\AnnotatedContainer\ContainerDefinitionBuilderContext $context,
    \Cspray\Typiphy\ObjectType $abstract,
    \Cspray\Typiphy\ObjectType $concrete
) : \Cspray\AnnotatedContainer\AliasDefinition;

\Cspray\AnnotatedContainer\serviceDelegate(
    \Cspray\AnnotatedContainer\ContainerDefinitionBuilderContext $context,
    \Cspray\Typiphy\ObjectType $service,
    \Cspray\Typiphy\ObjectType $factoryClass,
    string $factoryMethod
) : \Cspray\AnnotatedContainer\ServiceDelegateDefinition;

\Cspray\AnnotatedContainer\servicePrepare(
    \Cspray\AnnotatedContainer\ContainerDefinitionBuilderContext $context,
    \Cspray\Typiphy\ObjectType $service,
    string $method
) : \Cspray\AnnotatedContainer\ServicePrepareDefinition;

\Cspray\AnnotatedContainer\injectMethodParam(
    \Cspray\AnnotatedContainer\ContainerDefinitionBuilderContext $context,
    \Cspray\Typiphy\ObjectType $service,
    string $method,
    string $paramName,
    \Cspray\Typiphy\Type|\Cspray\Typiphy\TypeUnion|\Cspray\Typiphy\TypeIntersect $type,
    mixed $value,
    array $profiles = [],
    string $from = null
) : \Cspray\AnnotatedContainer\InjectDefinition;

\Cspray\AnnotatedContainer\injectProperty(
    \Cspray\AnnotatedContainer\ContainerDefinitionBuilderContext $context,
    \Cspray\Typiphy\ObjectType $service,
    string $property,
    \Cspray\Typiphy\Type|\Cspray\Typiphy\TypeUnion|\Cspray\Typiphy\TypeIntersect $type,
    mixed $value,
    array $profiles = [],
    string $from = null
) : \Cspray\AnnotatedContainer\InjectDefinition;
```

## Autowireable Parameters

```php
\Cspray\AnnotatedContainer\autowiredParams(
    \Cspray\AnnotatedContainer\AutowireableParameter... $parameters
) : \Cspray\AnnotatedContainer\AutowireableParameterSet;

\Cspray\AnnotatedContainer\serviceParam(
    string $name,
    \Cspray\Typiphy\ObjectType $service
) : \Cspray\AnnotatedContainer\AutowireableParameter;

\Cspray\AnnotatedContainer\rawParam(
    string $name,
    mixed $value
) : \Cspray\AnnotatedContainer\AutowireableParameter;
```
