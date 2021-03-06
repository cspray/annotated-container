# AnnotatedContainer

[![Unit Tests](https://github.com/cspray/annotated-container/actions/workflows/php.yml/badge.svg)](https://github.com/cspray/annotated-container/actions/workflows/php.yml)

A Dependency Injection framework for creating an autowired, feature-rich, [PSR-11](https://www.php-fig.org/psr/psr-11/) compatible Container using PHP 8 Attributes!

- Designate an interface as a Service and easily configure which concrete implementations to use
- Delegate service construction to a factory
- Inject scalar values, environment variables, and other services into your constructors and setters
- Automatically invoke methods after the service is constructed
- Use Profiles to easily use different services in different runtimes
- Create type-safe, highly flexible configuration objects
- Bring Your Own Container!

## Installation

```
composer require cspray/annotated-container
```

### Choose a Backing Container

AnnotatedContainer does not provide any of the actual Container functionality. We provide Attributes and definition objects that can determine how actual implementations are intended to be setup. AnnotatedContainer currently supports the following backing Containers:

```
composer require rdlowrey/auryn
```

Uses the [rdlowrey/auryn](https://github.com/rdlowrey/auryn) Injector.

```
composer require php-di/php-di:v7.x-dev
```

Uses the [php-di/php-di](https://github.com/php-di/php-di) Container. At the moment this library only supports the necessary 8.1 features in a development branch.

## Quick Start

Annotated Container ships with a built-in CLI tool to easily create a configuration detailing how to build your Container and a corresponding `Cspray\AnnotatedContainer\Bootstrap` implementation to create your Container using that configuration. It is highly recommended to utilize the CLI tool and configuration file when generating your Container.

> The CLI tool offers extensive documentation detailing how to run commands and what options are available. If you're ever looking for more info run: `./vendor/bin/annotated-container help`

The first step is to create the configuration. By default, the tooling will look at your `composer.json` to determine what directories to scan and create a directory that the ContainerDefinition can be cached in. Run the following command to complete this step. 

```
./vendor/bin/annotated-container init
```

The configuration file will be created in the root of your project named "annotated-container.xml". The cache directory will also be created in the root of your project named ".annotated-container-cache". Check out the command's help documentation for available options, including how to customize these values.

Be sure to review the generated configuration! A "normal" Composer setup might result in a configuration that looks like the following. If there are any directories that should be scanned but aren't listed in `<source></source>` be sure to include them. Conversely, if there are directories included that _shouldn't_ be scanned be sure to remove them.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
      <dir packagePrivate="true">tests</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
</annotatedContainer>
```

Now, bootstrap your Container in your app.

```php
<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Cspray\AnnotatedContainer\Bootstrap;

// Include other active profiles in this list
// If the only active profile is default you can call this method without any arguments
$profiles = ['default'];
$container = (new Bootstrap())->bootstrapContainer($profiles);
```

## Detailed Setup Guide

This is a short example to whet your appetite for learning more about AnnotatedContainer. Dependency resolution can be a complicated subject, and you should review the material in `/docs` if you plan on using the framework. Our example below is highly contrived but the rest of the documentation builds on top of it to show how AnnotatedContainer can help simplify dependency injection!

In our example we've been given a task to store some blob data. How that data might get stored will change over time, and we should abstract that change from the rest of the system. We decide to introduce a new interface and an initial implementation that will store the data on a filesystem.

```php
<?php

// interfaces and classes in __DIR__ . '/src'

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\InjectEnv;

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
require __DIR__ . '/vendor/autoload.php';

use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use function Cspray\AnnotatedContainer\compiler;
use function Cspray\AnnotatedContainer\containerFactory;

$containerDefinition = compiler()->compile(
    ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/src')->build()
);

// If you have not installed a backing container at this point you'll get an exception thrown 
$container = containerFactory()->createContainer($containerDefinition);

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

## Roadmap

The Roadmap can be found in the [annotated-container Project page](https://github.com/users/cspray/projects/1/views/1).