# Annotated Injector

A PHP8 library that will wire an [Auryn Injector](https://github.com/rdlowrey/auryn) based off of objects annotated with 
[Attributes](https://www.php.net/manual/en/language.attributes.php). Aims to provide functionality that enables 
configuring all Injector options through Attributes.

**Supported functionality**

- Share an interface annotated with `Service`.
- Alias concrete implementations of interfaces annotated with `Service`.
- Swap out concrete implementations based on environment. For example, run a cloud based storage in production, a 
local filesystem based storage in development, and a virtual filesystem in test.
- Execute arbitrary methods after service creation by annotating a method with `ServiceSetup`

This is a new library still in active development! Many features are planned for the future... please check out the 
Roadmap for more details on what's coming. Additionally, only the most basic use cases have been tested. It is not 
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

## Roadmap

### 0.1.x

- Compiler to parse Attributes from source directories ... :heavy_check_mark:
- Factory to create Injector based on parsed Attributes ... :heavy_check_mark:
- Support methods invoked in Injector::prepares ... :heavy_check_mark:
- Support defining scalar values on parameters ... :x:
- Support defining specific Service on parameters ... :x:

### 0.2.x

- Improved conflict resolution when multiple Services could satisfy an alias ... :x:
- Improve checks against erroneous uses of the library's attributes ... :x:
- Ensure that complex scenarios that could lead to Injector failure are accounted for ... :x:

### 0.3.x

- Support the concept of a Service factory ... :x:
- Support serializing and caching InjectorDefinition ... :x:

### 0.4.x

- Support defining scalar values from an arbitrary source ... :x:
