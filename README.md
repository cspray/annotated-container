# Annotated Injector

[![Unit Tests](https://github.com/cspray/annotated-injector/actions/workflows/php.yml/badge.svg)](https://github.com/cspray/annotated-injector/actions/workflows/php.yml)

A PHP8 library that will wire an [Auryn Injector](https://github.com/rdlowrey/auryn) based off of objects annotated with 
[Attributes](https://www.php.net/manual/en/language.attributes.php). Aims to provide functionality that enables 
configuring all Injector options through Attributes.

This is a new library still in active development! You should check out 2 things before you start using this library; 
the "KNOWN_ISSUES.md" file, and the "ROADMAP" in this README. While developing this library several known logical errors 
or resolution conflicts became known. Those issues are expected to be fixed in future versions, while the API is 
stabilizing however it is possible that you could run into these problems that could result in weird or unexpected 
behavior. When logical problems become known that we are planning on fixing, we'll update the "KNOWN_ISSUES.md" file.

:exclamation: :exclamation: Though this library is not yet production ready we're working hard to make it so! Please check back regularly for updates! :exclamation: :exclamation:

## Installation

```
composer require cspray/annotated-injector
```

## Getting Started

For a more complete, working, example check out the `examples/` directory. To get this example to work in your environment 
you'd have to move the interface and class definitions into the appropriate directory structure.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

// interfaces and classes in __DIR__ . '/src'

#[Service]
interface Foo {}

#[Service]
class FooImplementation {}

use Cspray\AnnotatedInjector\AnnotatedInjectorFactory;
use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\InjectorDefinitionCompiler;

$compiler = new InjectorDefinitionCompiler();
$injectorDefinition = $compiler->compileDirectory(__DIR__ . '/src', 'environment_identifier');
$injector = AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

var_dump($injector->make(Foo::class));
```

## How it Works

Dependency resolution can be a complicated subject, especially when a layer of syntactic sugar is laid on top of it. It 
is important before you use this library that you understand what it does, what opinions its made, any potential pitfalls, 
and how to mitigate or avoid them. We try to reduce or eliminate a lot of boilerplate around wiring an Injector, while 
also making your code more descriptive. That being said...

:exclamation: :exclamation: **Wiring your dependencies has serious implications. It is your responsibility to understand what you're doing 
when using this library and the Injector!** :exclamation: :exclamation:

### Service Attribute

The `Service` Attribute is the primary Attribute in the library and can be applied to interfaces, abstract classes, and 
concrete classes. What is a `Service`? A `Service` is an object that is shared with the `Injector` such that each time 
you call `Injector::make` the same object is returned. You can _share_ an interface and then _alias_ a concrete class 
so that any object created by the Injector will inject the concrete class where you type hint the interface! There's a 
lot more details than that, but the real power with the Injector comes in encouraging you to use interfaces to separate 
boundaries of your code. When you attach the `Service` Attribute we make some inferences about what you meant to do and 
then share or alias the `Service` as appropriate. We'll get into the specific details of what inferences we make later 
in this guide. For now, let's take a look at some code introducing a common design problem you'll encounter with the 
`Service` Attribute, and any autowiring system.

### Multiple Alias Resolution

Annotating autowiring functionality introduces an issue this library calls the "multiple alias resolution" problem. 
Based on the annotations the library believes that there are multiple possible concrete implementations that could 
satisfy a shared interface. Let's take a look at a naive example:

```php
<?php

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
interface Foo {}

#[Service]
class Bar implements Foo {}

#[Service]
class Baz implements Foo {}

#[Service]
class Qux implements Foo {}

#[Service]
class FooConsumer {

    public function __construct(private Foo $foo) {}

}
```

With the above example, when the `FooConsumer` is instantiated how does the library determine which 
implementation of `Foo` to pass? There are 3 implementations that could potentially satisfy this 
requirement, and we don't have enough context to pick one. Hence, the multiple alias resolution problem. Ultimately, 
fixing this problem lies on your shoulders. You have to tell the library how to resolve these type of 
resolution problems. There are 2 ways the library provides to allow you to easily do that.

#### Environment Resolution

The first method for clarifying alias resolution is by using the environment your application is running in. This 
strategy is especially useful if you have different implementations you want to use on your dev machine, 
versus CI, versus your production environment. The first thing we'll want to do is specify which concrete implementations 
we want to use for each environment. Let's take a look at our example again and make some modifications:

> The `examples/getting_started` directory has a working example of this concept!

```php
<?php

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
interface Foo {}

#[Service(environments: ['test'])]
class Bar implements Foo {}

#[Service(environments: ['dev'])]
class Baz implements Foo {}

#[Service(environments: ['prod'])]
class Qux implements Foo {}

#[Service]
class FooConsumer {

    public function __construct(private Foo $foo) {}

}
```

Now when you call `InjectorDefinitionCompiler::compileDirectory` you can pass in `'test'`, `'dev'`, or `'prod'` and the 
corresponding concrete implementation will be used! Implementing this strategy will allow the library to smartly realize 
that one of the implementations can be aliased and now any place you declare `Foo` and use the `Injector` the corresponding 
concrete implementation will be injected. Nothing else to do here! This problem is solved!

If you need more control over your injections, or you have multiple implementations that are used in more than 1 environment 
you can get more specific with the `DefineService` Attribute.

#### DefineService Resolution

It is possible to annotate parameters in `Service` constructors, as well as methods annotated with `ServicePrepare`, with 
`DefineService` Attribute. This will inject for the specific method's parameter the defined `Service`. Let's take a look 
at our example but resolve the problem with `FooConsumer` for this specific parameter.

```php
<?php

use Cspray\AnnotatedInjector\Attribute\DefineService;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
interface Foo {}

#[Service]
class Bar implements Foo {}

#[Service]
class Baz implements Foo {}

#[Service]
class Qux implements Foo {}

#[Service]
class FooConsumer {

    public function __construct(
        #[DefineService(Qux::class)]
        private Foo $foo
    ) {}

}
```

### Defining Scalar Values

#### Constant Resolution

#### Reading from env vars

### Preparing Services

## Attributes Overview

The following Attributes are made available through this library. All Attributes listed are under the namespace 
`Cspray\AnnotatedInjector\Attribute`. 

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
- Support defining specific Service on parameters ... :heavy_check_mark:

### 0.2.x

- Support defining scalar values from an arbitrary source ... :x:
- Support the concept of a Service factory ... :x:
- Support serializing and caching InjectorDefinition ... :x:
- Improve handling of low-hanging fruit logical errors... :x:

### 0.3.x

- Improve handling of logical errors... :x:
- Harden library for production use ... :x:

### 0.4.x and beyond

- Add support for amphp/injector ... :x:
- Support working with identifiers once feature is in amphp/injector ... :x:
- Research potentially supporting other containers ... :question:
- Further improve library's use in production environment ... :x: