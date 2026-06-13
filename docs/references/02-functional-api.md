# Functional API

Annotated Container provides a series of functions that are designed to:

- Provide a mechanism for defining services that can't be annotated
- Define a standardized way for passing arguments to `AutowireableFactory` and `AutowireableInvoker`.

This document lists the functions for each purpose.

## Defining Services

```php
<?php

use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\Reflection\TypeUnion;
use Cspray\AnnotatedContainer\Reflection\TypeIntersect;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use function Cspray\AnnotatedContainer\Definition\inject;
use function Cspray\AnnotatedContainer\Definition\service;
use function Cspray\AnnotatedContainer\Definition\serviceDelegate;
use function Cspray\AnnotatedContainer\Definition\servicePrepare;

service(
    Type $service,
    ?string $name = null,
    array $profiles = [],
    bool $isPrimary = false
) : ServiceDefinition;

serviceDelegate(
    Type $factoryClass,
    string $factoryMethod,
    array $profiles = []
) : ServiceDelegateDefinition;

servicePrepare(
    Type $service,
    string $method
) : ServicePrepareDefinition;

inject(
    Type $service,
    string $method,
    string $paramName,
    Type|TypeUnion|TypeIntersect $type,
    mixed $value,
    array $profiles = [],
    string $from = null
) : InjectDefinition;

```

## Autowireable Parameters

```php
<?php

use Cspray\AnnotatedContainer\Autowire\AutowireableParameter;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameterSet;
use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Autowire\autowiredParams;
use function Cspray\AnnotatedContainer\Autowire\rawParam;
use function Cspray\AnnotatedContainer\Autowire\serviceParam;

autowiredParams(
    AutowireableParameter... $parameters
) : AutowireableParameterSet;

rawParam(
    string $name,
    mixed $value
) : AutowireableParameter;

serviceParam(
    string $name,
    Type $service
) : AutowireableParameter;
```
