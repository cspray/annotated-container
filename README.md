# Annotated Injector

[![Unit Tests](https://github.com/cspray/annotated-injector/actions/workflows/php.yml/badge.svg)](https://github.com/cspray/annotated-injector/actions/workflows/php.yml)

A PHP8 library that will wire an [Auryn Injector](https://github.com/rdlowrey/auryn) based off of objects annotated with 
[Attributes](https://www.php.net/manual/en/language.attributes.php). Aims to provide functionality that enables 
configuring all Injector options through Attributes.

This is a new library still in active development! Many features are planned for the future... please check out the 
Roadmap for what has been accomplished and what's coming. Additionally, only the most basic use cases have been tested. It is not 
recommended using this library in production at this time. We're aiming to be production ready soon though!

## Installation

```
composer require cspray/annotated-injector
```

## Getting Started

For a more complete, working example check out the `examples/` directory. To get this example to work in your environment 
you'd have to move the interface and class definitions into the appropriate directory structure.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

// interfaces and classes in src/

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
interface Foo {}

#[Service]
class FooImplementation {}

use Cspray\AnnotatedInjector\InjectorDefinitionCompiler;

$compiler = new InjectorDefinitionCompiler();
$injectorDefinition = $compiler->compileDirectory(__DIR__ . '/src', 'environment_identifier');
$injector = \Cspray\AnnotatedInjector\AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

var_dump($injector->make(Foo::class));
```

## Attributes Overview

The following Attributes are made available through this library. All Attributes listed are under the namespace 
`Cspray\AnnotatedInjector\Attribute`. This is just a brief description of the available Attributes. Please read the 
source documentation for more detailed information. 

|Attribute Name | Target | Description
--- | --- | ---
|`Service`|`Attribute::TARGET_CLASS`|Describes an interface, abstract class, or concrete class as being a service. Will share and alias the types into the Injector based on what's annotated.|
|`ServicePrepare`|`Attribute::TARGET_METHOD`|Describes a method, on an interface or class, that should be invoked when that type is created.|
|`DefineScalar`|`Attribute::TARGET_PARAMETER`|Defines a scalar parameter on a Service constructor or ServicePrepare method. The value will be the exact value passed to this Attribute.|
|`DefineScalarFromEnv`|`Attribute::TARGET_PARAMETER`|Defines a scalar parameter on a Service constructor or ServicePrepare method. The value will be taken from an environment variable matching this Attribute's value|
|`DefineService`|`Attribute::TARGET_PARAMETER`|Defines a Service parameter on a Service constructor or ServicePrepare method.|

## Roadmap

### 0.1.x

- Compiler to parse Attributes from source directories ... :heavy_check_mark:
- Factory to create Injector based on parsed Attributes ... :heavy_check_mark:
- Support methods invoked in Injector::prepares ... :heavy_check_mark:
- Support defining scalar values on parameters ... :heavy_check_mark:
- Support defining specific Service on parameters ... :x:

### 0.2.x

- Support defining scalar values from an arbitrary source ... :x:
- Support the concept of a Service factory ... :x:
- Support serializing and caching InjectorDefinition ... :x:

### 0.3.x

- Improved conflict resolution when multiple Services could satisfy an alias ... :x:
- Improve checks against erroneous uses of the library's attributes ... :x:
- Ensure that complex scenarios that could lead to Injector failure are accounted for ... :x:
  
### 0.4.x and beyond

- Transition to amphp/injector ... :x:
- Support working with identifiers once feature is in amphp/injector ... :x:
- Further improve library's use in production environment ... :x: