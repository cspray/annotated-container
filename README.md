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

:exclamation: Though this library is not yet production ready we're working hard to make it so! Please check back regularly for updates!

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

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
interface Foo {}

#[Service]
class FooImplementation {}

// app bootstrap in __DIR__ . '/app.php'

use Cspray\AnnotatedInjector\AurynInjectorFactory;
use Cspray\AnnotatedInjector\PhpParserInjectorDefinitionCompiler;

$compiler = new PhpParserInjectorDefinitionCompiler();
$injectorDefinition = $compiler->compileDirectory('env_identifier', __DIR__ . '/src');
$injector = (new AurynInjectorFactory)->createContainer($injectorDefinition);

var_dump($injector->make(Foo::class));
```

Dependency resolution can be a complicated subject, especially when a layer of syntactic sugar is laid on top of it. It
is important before you use this library that you understand what opinions it has made and how to use it properly. This
README aims to be an exhaustive resource for using the Attributes properly as well as for writing internal code. If you're 
looking to start using the Attributes to configure your `Injector` check out the "User Guide". Otherwise, check out 
"How it Works" for a detailed look at the underpinnings of the library.

:exclamation: **Wiring your dependencies has serious implications. It is your responsibility to understand what you're doing
when using this library and the Injector!**

## User Guide

This guide aims to get you up to speed with how to properly use the library and considerations you should have while 
annotating your object graph. At the end of this guide you should have a solid understanding of how to use all of the 
Attributes that are implemented in this library.

> This User Guide details only implemented features. As new features or Attributes are implemented their details will 
> be added to this User Guide!

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

    private Foo $foo;

    public function __construct(Foo $foo) {
        $this->foo = $foo;
    }

}
```

With the above example, when the `FooConsumer` is instantiated how does the library determine which implementation 
of `Foo` to pass? There are 3 implementations that could potentially satisfy this requirement, and we don't have 
enough context to pick one. Hence, the multiple alias resolution problem. If you were to instantiate `FooConsumer` 
in this example you'd get an `InjectionException` as the container hasn't been properly configured.

Ultimately, fixing this problem lies on your shoulders. You have to tell the library how to resolve these type of 
resolution problems. There are 2 straight-forward ways to do that.

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

    private Foo $foo;

    public function __construct(Foo $foo) {
        $this->foo = $foo;
    }

}
```

Now when you call `InjectorDefinitionCompiler::compileDirectory` you can pass in `'test'`, `'dev'`, or `'prod'` and the 
corresponding concrete implementation will be used! Implementing this strategy will allow the library to smartly realize 
that one of the implementations can be aliased and now any place you declare `Foo` and use the `Injector` the corresponding 
concrete implementation will be injected. Nothing else to do here! This problem is solved throughout your codebase!

If you need more control over your injections, or you have multiple implementations that are used in more than 1 environment 
you can get more specific with the `UseService` Attribute.

#### UseService Resolution

It is possible to annotate parameters in `Service` constructors, as well as methods annotated with `ServicePrepare`, with 
`UseService` Attribute. This will inject for the specific method's parameter the defined `Service`. Let's take a look 
at our example but resolve the problem with `FooConsumer` for this specific parameter.

```php
<?php

use Cspray\AnnotatedInjector\Attribute\UseService;
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

    private Foo $foo;

    public function __construct(
        #[UseService(Qux::class)]
        Foo $foo
    ) {
        $this->foo = $foo;
    }

}
```

Now when we instantiate `FooConsumer` the `Qux` implementation is injected. It is important to note in this use case we 
_do not_ alias any of the implementations above. It is up to you to ensure that you're using `UseService` in 
appropriate places or that you're specifying these dependencies when you make the object.

Sometimes your `Service` requires a string or something that can't be provided by the `Injector` nor another `Service`. 
We've got you covered! Next up we'll talk about how to define scalar values your `Service` may need.

### Injected Scalar Values

It is expected that at some point your `Service` will require some scalar value. A prime example is when your `Service` 
communicates with an HTTP API and has to provide credentials. Let's take a look at a naive example:

```php
<?php

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class FooWebClient {

    private string $clientId;
    private string $apiSecret;

    public function __construct(string $clientId, string $apiSecret) {
        $this->clientId = $clientId;
        $this->apiSecret = $apiSecret;
    }

}
```

Without providing explicit parameter values when you call `Injector::make` the above code will throw an `InjectionException` 
as the container can't resolve scalar values. You can fix this with a series of Attributes that defines the scalar 
value that should be used.

#### Hardcoded Values

If your desired values can be hardcoded into the `UseScalar` Attribute. There's nothing fancy here... just put the 
Attribute on the parameter with the desired value. Our previous example, properly annotated:

```php
<?php

use Cspray\AnnotatedInjector\Attribute\UseScalar;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class FooWebClient {

    private string $clientId;
    private string $apiSecret;

    public function __construct(
        #[UseScalar("my-client-id")]
        string $clientId,
        #[UseScalar("my-api-secret")]
        string $apiSecret
    ) 
{
        $this->clientId = $clientId;
        $this->apiSecret = $apiSecret;
    }

}
```

Now when we make `FooWebClient` the strings `"my-client-id"` and `"my-api-secret"` will be passed to the appropriate 
parameters! Hardcoded values may be the appropriate way to handle some parameters. It probably isn't the correct 
way for this implementation. Chances are you won't be able to hardcode these type of values. Additionally, if you 
don't-even-look-that-close you can see a security problem with the above code. We'll talk about the security flaw 
later but for now let's take a look at how to avoid hard-coding scalar values. The library provides an additional 
Attribute for reading a value from an environment variable.

#### Reading from env vars

Let's take our previous example and improve it by reading in our client id and secret from environment variables.

```php
<?php

use Cspray\AnnotatedInjector\Attribute\UseScalarFromEnv;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class FooWebClient {

    private string $clientId;
    private string $apiSecret;

    public function __construct(
        #[UseScalarFromEnv('MY_CLIENT_ID')]
        string $clientId,
        #[UseScalarFromEnv('MY_API_SECRET')]
        string $apiSecret
    ) {
        $this->clientId = $clientId;
        $this->apiSecret = $apiSecret;
    }

}
```

Now when you construct `FooWebClient` we'll inject our client id and secret from values stored in the environment. This 
way your secrets can remain out of your codebase, and you aren't burdened by hardcoded values in the codebase! 

Next, it is important to understand how the library resolves "dynamic" scalar values that change at runtime.

#### Resolving Dynamic Scalar Values

When you pass `UseScalar` a constant or everytime you use `UseScalarEnv` you're requesting a value that cannot 
be reliably determined at compile team when we parse your Attributes. For constants the value could potentially be 
different based on the environment you're running in and how you load those constants. For environment variables their 
intrinsic nature means they could be different on the machine that does the compiling versus the machine that does the 
running. To ensure that you don't get bad values we defer the gathering of constant values and environment variables 
until runtime.

When we encounter the `UseScalarFromEnv` Attribute or that you're fetching a constant we store a decorated value 
for this Attribute. In the example above we wouldn't gather the environment variable during compile time, instead we 
would store the values  as `!env(MY_CLIENT_ID)` and `!env(MY_API_SECRET)`, respectively. At runtime when we parse 
the compiled definitions we recognize that these values should be derived from an environment variable and make the 
appropriate PHP calls to read `MY_CLIENT_ID` and `MY_API_SECRET` from the environment. Constants follow a similar 
pattern with `!env()` replaced with `!const()`.

#### UseScalar Security Considerations

It is important to keep in mind that the compiled `InjectionDefinition` can be serialized and is intended to be 
cached in production environments. This means, **the values you pass to the Attributes of this library are stored 
in plaintext in whatever caching mechanism you use**. This means in our `UseScalar` example when we annotate 
`UseScalar("my-api-secret")` we are committing a mistake and introducing a security flaw into our codebase! Even if 
the repository you're working in is "private" the serialized representation of your annotations could be stored in an 
insecure manner! You should be cautious about how to use `UseScalar` and defer to using one of the other attributes 
available when dealing with scalar values.

### Preparing Services

Sometimes you'll need to do something with a `Service` after it has been instantiated to do something that can't easily 
be done during object construction. While constructor injection is preferred sometimes it isn't desirable or you're working 
with an API that makes it hard to accomplish. An example of this could be the use of the `Psr\Log\LoggerAwareInterface`.
The `ServicePrepare` Attribute helps solve the problem with setter injection by ensuring your setters are called on 
object instantiation. Let's take a look at an example.

```php
use Cspray\AnnotatedInjector\Attribute\Service;
use Cspray\AnnotatedInjector\Attribute\ServicePrepare;

#[Service]
class Foo implements Psr\Log\LoggerAwareInterface {

    private Psr\Log\LoggerInterface $logger;
    
    // implementation details that are way too complex to have a LoggerInterface in the constructor

    #[ServicePrepare]    
    public function setLogger(Psr\Log\LoggerInterface $logger) {
        $this->logger = $logger;
    }


}
```

In our above example when we construct `Foo` we'll also invoke `setLogger` using the `Injector::execute` method. Meaning,
if your `LoggerInterface` is defined on the `Injector` your `$logger` property will always be set as long you're creating 
it with `Injector::make`! A class or interface can define as many `ServicePrepare` methods as is appropriate. However, 
if you're relying on this functionality too much it could be a code smell and that you should be relying more on 
constructor injection.

## Using a Factory

Sometimes a Service needs to be constructed by a Factory. Fortunately there's a simple way to declare a class method as 
a factory method. Annotate any class method with `#[ServiceDelegate()]` and pass the type the factory creates.

```php
<?php

use \Cspray\AnnotatedInjector\Attribute\Service;
use \Cspray\AnnotatedInjector\Attribute\ServiceDelegate;

#[Service]
interface ServiceInterface {}

class ServiceFactory {

    #[ServiceDelegate(ServiceInterface::class)]
    public function create() : ServiceInterface {
        return new class implements ServiceInterFace {};
    }

}

?>
```

With this configuration anytime you declare `ServiceInterface` the `ServiceFactory::create` method will be used to create 
the object. The factory method is executed with `Injector::execute`, meaning your factory can depend on other services 
as long as they can be properly instantiated by the injector!

## Attributes Overview

The following Attributes are made available through this library. All Attributes listed are under the namespace 
`Cspray\AnnotatedInjector\Attribute`. 

|Attribute Name | Target | Description|Implemented|
--- | --- | --- | ---
|`Service`|`Attribute::TARGET_CLASS`|Describes an interface, abstract class, or concrete class as being a service. Will share and alias the types into the Injector based on what's annotated.|:heavy_check_mark:|
|`ServicePrepare`|`Attribute::TARGET_METHOD`|Describes a method, on an interface or class, that should be invoked when that type is created.|:heavy_check_mark:|
|`UseScalar`|`Attribute::TARGET_PARAMETER`|Defines a scalar parameter on a Service constructor or ServicePrepare method. The value will be the exact value passed to this Attribute.|:heavy_check_mark:|
|`UseScalarFromEnv`|`Attribute::TARGET_PARAMETER`|Defines a scalar parameter on a Service constructor or ServicePrepare method. The value will be taken from an environment variable matching this Attribute's value|:heavy_check_mark:|
|`UseService`|`Attribute::TARGET_PARAMETER`|Defines a Service parameter on a Service constructor or ServicePrepare method.|:heavy_check_mark:|
|`ServiceDelegate`|`Attribute::TARGET_METHOD`|Defines a method that will be used to generate a defined type.|:heavy_check_mark:|
|`UseScalarFromParamStore`|`Attribute::TARGET_PARAMETER`|Defines a scalar parameter on a Service constructor or ServicePrepare method. The value will be taken from an interface responsible for providing values to annotated parameters.|:x:|
|`UseServiceFromParamStore`|`Attribute::TARGET_PARAMETER`|Defines a Service parameter on a Service constructor or ServicePrepare method. The value will be taken from an interface responsible for providing values to annotated paramters.|:x:|

## Roadmap

### 0.1.x

- Compiler to parse Attributes from source directories ... :heavy_check_mark:
- Factory to create Injector based on parsed Attributes ... :heavy_check_mark:
- Support methods invoked in Injector::prepares ... :heavy_check_mark:
- Support defining scalar values on parameters ... :heavy_check_mark:
- Support defining specific Service on parameters ... :heavy_check_mark:

### 0.2.x

- Support the concept of a Service factory ... :heavy_check_mark:
- Support serializing and caching InjectorDefinition ... :x:
- Improve handling of low-hanging fruit logical errors... :x:

### 0.3.x

- Support defining scalar values from an arbitrary source ... :x:
- Improve handling of logical errors... :x:
- Harden library for production use ... :x:

### 0.4.x and beyond

- Add support for amphp/injector ... :x:
- Support working with identifiers once feature is in amphp/injector ... :x:
- Research potentially supporting other containers ... :question:
- Further improve library's use in production environment ... :x: