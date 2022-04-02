# AnnotatedContainer

[![Unit Tests](https://github.com/cspray/annotated-container/actions/workflows/php.yml/badge.svg)](https://github.com/cspray/annotated-container/actions/workflows/php.yml)

A Dependency Injection framework for creating an autowired, feature-rich, [PSR-11](https://github.com/cspray/annotated-container-dummy-apps) compatible Container using PHP 8 Attributes!

- Compile and analyze the configuration for a Container without ever running any code
- Designate an interface as a Service and easily configure which concrete implementations to use
- Delegate service construction to a factory
- Inject scalar values, environment variables, and other services into your constructors and setters
- Automatically invoke methods after the service is constructed
- Use Profiles to easily use different services in different runtimes

## Installation

```
composer require cspray/annotated-container
```

## Quick Start

This is a short example to whet your appetite for learning more about AnnotatedContainer. Dependency resolution can be a complicated subject, and you should review the material in `/docs` if you plan on using the framework. Our example below is highly contrived but the rest of the documentation builds on top of it to show how AnnotatedContainer can help simplify dependency injection!

In our example we've been given a task to store some blob data. How that data might get stored will change over time, and we should abstract that change from the rest of the system. We decide to introduce a new interface and an initial implementation that will store the data on a filesystem.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

// interfaces and classes in __DIR__ . '/src'

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\InjectEnv;
use Cspray\AnnotatedContainer\AurynContainerFactory;

#[Service]
interface BlobStorage {

    public function store(string $identifier, string $contents) : void;
    
    public function retrieve(string $identifier) : ?string;

}

#[Service]
class FilesystemStorage implements BlobStorage {
    
    public function store(string $identifier, string $contents) : void {
        file_put_contents($identifier, $contents);
    }
    
    public function retrieve(string $identifier) : ?string {
        return file_get_contents($identifier) ?? null;
    }

}

// app bootstrap in __DIR__ . '/app.php'

use Cspray\AnnotatedContainer\AurynContainerFactory;
use Cspray\AnnotatedContainer\PhpParserInjectorDefinitionCompiler;
use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerDefinitionCompilerFactory;

$compiler = ContainerDefinitionCompilerFactory::withoutCache()->getCompiler();
$containerDefinition = $compiler->compile(
    ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/src')->build()
);

$container = (new AurynContainerFactory)->createContainer($containerDefinition);

var_dump($container->get(BlobStorage::class) instanceof FilesystemStorage); // true
var_dump($container->get(BlobStorage::class) === $container->get(BlobStorage::class)); // true
```

While far from something you'd want to use in production our example shows that we can create a Container that can infer what concrete implementation to use from the name of an interface. Pretty neat! There's a lot more functionality available to you and this is just the tip of the proverbial iceberg.

Dependency resolution can be a complicated subject, especially when a layer of syntactic sugar is laid on top of it. It is important before you use this library, or any other, that you understand what opinions it has made and how to use it properly. The articles in `/docs` in the root of this repo will provide more information. Additionally, many use cases have been created and tested in the repo's test suite. Reviewing those tests could also show you a lot of information for how the library works.

## Documentation

This library is thoroughly documented in-repo under the `/docs` directory. The documentation is split into three parts; Tutorials, How Tos, and References.

**Tutorials** are where you'll want to start. It'll expand on the examples in the "Quick Start" and teach you how to do most of the things you'll want to do with the library. This documentation tends to split the difference between the amount of code and the amount of explanation.

**How Tos** are where you'll go to get step-by-step guides on how to achieve specific functionality. These documents tend to be more code and less explanation. We assume you've gotten an understanding of the library and have questions on how to do something beyond the "normal" use cases. 

**References** are where you can get into the real internal, technical workings of the library. List of APIs and more technically-explicit documentation can be found here. References may be a lot of code, a lot of explanation, or a split between the two depending on the context.

> The documentation is still a work in progress and some sections may be missing or incomplete relative to the desired 
> 1.0 functionality. Eventually this documentation will be provided in a website form.

## Roadmap

### 0.1.x

- Compiler to parse Attributes from source directories ... :heavy_check_mark:
- Factory to create Container based on parsed Attributes ... :heavy_check_mark:
- Support methods invoked in Injector::prepares ... :heavy_check_mark:
- Support defining scalar values on parameters ... :heavy_check_mark:
- Support defining specific Service on parameters ... :heavy_check_mark:

### 0.2.x

- Support the concept of a Service factory ... :heavy_check_mark:
- Support a PSR ContainerInterface Factory ... :heavy_check_mark:
- Support serializing and caching ContainerDefinition ... :heavy_check_mark:
- Handle when abstract Service does not have corresponding alias ... :heavy_check_mark:
- Handle when an abstract Service might have more than 1 alias ... :heavy_check_mark:
- Support Profiles instead of Environments ... :heavy_check_mark:
- Support creating a ContainerDefinition for libraries that can't be Annotated ... :heavy_check_mark:

### 0.3.x

- Support the concept of a Service that is not shared, instead is recreated on every retrieval ... :x:
- Support a Service being marked as primary to be used for multiple alias resolution ... :heavy_check_mark:
- Support a Service having an explicit name that is not the FQCN ... :x:
- Support a ServiceCollection Attribute which allows collecting many Services ... :x:
- Support easily configuring third-party code that can't be annotated ... :x:
- Support profiles when injecting scalar values ... :heavy_check_mark:

### 0.4.x

- Support creating a Container with different backing libraries ... :x:
- Have convenience functions for common ways of interacting with the library ... :x:
- Solidify and document the process one would take for deploying this code in production ... :x:

### 1.0 and beyond

- Improve handling of logical errors... :x:
- Host documentation on website ... :x:
- Support defining scalar values from an arbitrary source ... :question: