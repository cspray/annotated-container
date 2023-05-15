# Bootstrap Your Container

As Annotated Container has added more and more functionality the bootstrapping it requires has necessarily grown. It is possible to get up and running without using the provided tooling, but we highly recommend using the CLI tool and corresponding `Cspray\AnnotatedContainer\Bootstrap` to create your Container. This document details how to take advantage of Annotated Container's functionality using this tooling.

## Step 1 - Init Your Configuration

The first step is to create a configuration file that details how Annotated Container should bootstrap itself. As long as you have a `composer.json` in your project's root directory, and it defines at least one directory with a PSR-4 or PSR-0 namespace then the tooling can figure out which directories to scan. Run the following shell command:

```shell
./vendor/bin/annotated-container init
```

If successful you'll get a configuration file named `annotated-container.xml` in the root of your project. In most setups it'll look something like this:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
      <dir>tests</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
</annotatedContainer>
```

The most important, and the only thing that's actually required, is to define at least 1 source directory to scan. By default, we also include a directory that stores a cached ContainerDefinition. Though the cache directory is not strictly required it is a good practice to include it. Caching drastically increases the performance of creating your Container. It is also required if you want to use the `./vendor/bin/annotated-container build` command to generate a ContainerDefinition ahead-of-time, for example in production.

The rest of this guide will add new elements to this configuration. The steps below are optional, if you don't require any "bells & whistles" skip to Step 5.

## Step 2 - Setup Logging (optional)

Annotated Container statically analyzes your codebase for uses of specific Attributes and then wires all that stuff into a usable Container. When you consider all the different responsibilities this adds up to a lot of things happening. Logging provides every single detail for what is parsed and how that gets turned into a Container.

Bootstrapping allows logging to a file or logging to stdout. In our example the logs will be sent to a file in `logs/` directory located in our project's root. You should ensure that the `logs/` directory exists and is writable.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
      <dir>tests</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
  <logging>
    <file>logs/annotated-container.log</file>
  </logging>
</annotatedContainer>
```

## Step 3 - Setup Third Party Services (optional)

To define services that can't be annotated you can make use of a `Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider` implementation. Implementing this interface allows you to use the [functional API](../references/03-functional-api.md) to augment your ContainerDefinition without using Attributes. Out-of-the-box, it is expected this implementation will have a zero-argument constructor. Later on in this document I will discuss ways that you can override construction if your implementation has dependencies. Primarily this should be used to integrate third-party libraries that can't have Attributes assigned to them.

Somewhere in your source code:

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;

class ThirdPartyServicesProvider implements DefinitionProvider {

    public function consume(DefinitionProviderContext $context) : void {
        // Make calls to the functional API to add 
    }

}
```

Now, upgrade the configuration to let bootstrapping know which class to use.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
      <dir>tests</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
  <definitionProviders>
    <definitionProvider>Acme\Demo\ThirdPartyServicesProvider</definitionProvider>
  </definitionProviders>
</annotatedContainer>
```

### Step 4 - Provide your custom ParameterStore (optional)

In any sufficiently large enough application you'll probably want to take advantage of parameter stores to have complete programmatic control over what non-service values get injected. You can define a list of ParameterStore implementations that should be added during bootstrapping. Out-of-the-box, it is expected that these implementations will have a no argument constructor. Later in this document I'll go over how to override construction of these implementations if they require dependencies.

Somewhere in your source code:

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\Annotatedcontainer\ContainerFactory\ParameterStore;

final class MyCustomParameterStore implements ParameterStore {

    
    public function getName() : string {
        return 'my-store';
    }

    public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : mixed {
        // do something to fetch the $key and return the value
    }

}
```

Next, update your configuration.


```xml
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
      <dir>tests</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
  <parameterStores>
    <parameterStore>Acme\Demo\MyCustomParameterStore</parameterStore>
  </parameterStores>
</annotatedContainer>
```

### Step 5 - Create Your Container

Before completing this step go put some Attributes on the services in your codebase!

Now that the configuration file has been modified, and you've attributed your codebase, to fit your needs you can create your container! If you're using only out-of-the-box functionality this can be done with the following code snippet.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;

$container = (new Bootstrap())->bootstrapContainer();
```

You have the ability to control specific aspects of the Bootstrapping process by providing different arguments to the `bootstrapContainer` method and different dependencies to the constructor. I'll talk about the method arguments first, then take a look at how you can provide different constructor dependencies.

#### Specifying Profiles

The first argument, `$profiles`, passed to `bootstrapContainer` should be an array of active profiles. If you don't pass any arguments the default value `['default']` will be used.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;

// If you specify 'dev', 'test, 'prod' profiles this container will be ready for production
$container = (new Bootstrap())->bootstrapContainer(profiles: ['default', 'prod']);
```

#### Changing the Configuration File

Perhaps you didn't name your configuration file the default, it is recommended you do so but perhaps there's good reasons to change it. You can pass the second argument, `$configurationFile`, to `bootstrapContainer` that defines the name of the configuration file. If you don't pass any arguments the default value `annotated-container.xml` will be used.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;

// If you specify 'dev', 'test, 'prod' profiles this container will be ready for production
$container = (new Bootstrap())->bootstrapContainer(configurationFile: 'my-container.xml');
```

#### Overriding Logging

Perhaps the out-of-the-box logging provided by Annotated Container isn't sufficient. Perhaps you're working in an app with a logging system already in place. Whatever the reason, if you provide a `Psr\Log\LoggerInterface` to the `$logger` construct argument that implementation will be used.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;

// This method is an implementation provided by the reader
$logger = MyLoggerFactory::create();

$container = (new Bootstrap(logger: $logger))->bootstrapContainer();
```

**Any logger passed to this constructor will override the logging set in the configuration!**

#### Constructing DefinitionProvider 

There might be dependencies you need to determine what third-party services should be included in your `DefinitionProvider` implementations. If so, there's a `Cspray\AnnotatedContainer\Bootstrap\DefinitionProviderFactory` interface that you can implement and then pass that instance to the `$definitionProviderFactory` construct argument.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;

// This method is an implementation provided by the reader
$definitionProviderFactory = MyDefinitionProviderFactory::create();

$container = (new Bootstrap(
   definitionProviderFactory: $definitionProviderFactory 
))->bootstrapContainer();
```

#### Constructing ParameterStore

The custom `ParameterStore` implementations you use might require some dependency to gather the appropriate values. In this case, the `Cspray\AnnotatedContainer\Bootstrap\ParameterStoreFactory` interface can be implemented and passed to the `$parameterStoreFactory` construct argument.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;

// This method is an implementation provided by the reader
$parameterStoreFactory = MyParameterStoreFactory::create();

$container = (new Bootstrap(
    parameterStoreFactory: $parameterStoreFactory
))->bootstrapContainer();
```

#### Changing Resolved Paths

By default, boostrapping expects all the path fragments in your configuration to be in the root of your project. You can have explicit control over which absolute path is used by implementing a `Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver` and passed to `$directoryResolver` contruct argument.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap;

// This method is an implementation provided by the reader
$directoryResolver = MyDirectoryResolver::create();

$container = (new Bootstrap(
    directoryResolver: $directoryResolver
))->bootstrapContainer();
```
