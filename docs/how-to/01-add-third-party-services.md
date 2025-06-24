# Adding Third-Party Services

It is very likely that you'll need to add some service to the Container that can't be annotated. AnnotatedContainer offers a set of functions to easily add third-party services with all the feature-parity and functionality available to annotated code. This guide goes through a step-by-step guide on how to integrate the popular [Monolog](https://github.com/Seldaek/monolog) library with [PSR-3](https://www.php-fig.org/psr/psr-3/) services. 

Starting with Annotated Container 2.3.0, new functionality was added that allows much easier, implicit setup of third-party services. The "Implicit Setup", detailed below, is the preferred method of adding third-party services to your container. The "Explicit Setup" details what was the documented approach for versions prior to 2.3. It also uses an approach that does not rely on Attributes of any kind. If you're using Annotated Container without Attributes, this is the preferred approach for your use case.

> This guide assumes a basic understanding on how to interact with this library. If you're unsure of something we discuss here it is recommended you checkout the rest of the /docs/tutorials section.


## Implicit Setup

## Step 1 - Install PSR-3 and Monolog

```shell
composer require monolog/monolog psr/log
```

## Step 2 - Create Factory and Assign ServiceDelegate

```php
<?php

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

class MonologLoggerFactory {

    #[ServiceDelegate]
    public function createLogger() : LoggerInterface {
        $log = new Logger('app-name');
        $log->pushHandler(new StreamHandler('php://stdout'));
        
        return $log;
    }

}
```

This is all that's required for the implicit setup. When we encounter a `#[ServiceDelegate]` for a type that has not been defined as a service, we add the service implicitly and assign the appropriate factory method for creating that service.

## Explicit Setup

## Step 1 - Install PSR-3 and Monolog

```shell
composer require monolog/monolog psr/log
```

## Step 2 - Create Factory

```php
<?php

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

class MonologLoggerFactory {

    public function createLogger() : LoggerInterface {
        $log = new Logger('app-name');
        $log->pushHandler(new StreamHandler('php://stdout'));
        
        return $log;
    }

}
```

## Step 3 - Define a DefinitionProvider

```php
<?php

use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use function Cspray\AnnotatedContainer\Definition\service;
use function Cspray\AnnotatedContainer\Definition\serviceDelegate;
use function Cspray\AnnotatedContainer\Definition\servicePrepare;
use function Cspray\AnnotatedContainer\Reflection\types;

class ThirdPartyServicesProvider implements DefinitionProvider {

    public function consume(DefinitionProviderContext $context) : void {
        $context->addServiceDefinition(
            service(types()->class(LoggerInterface::class))
        );
        $context->addServiceDelegateDefinition(
            serviceDelegate(
                types()->class(MonologLoggerFactory::class), 'createLogger'
            )
        );
        $context->addServicePrepareDefinition(
            servicePrepare(
                types()->class(LoggerAwareInterface::class), 'setLogger'
            )
        );
    }
}

```

## Step 4 - Update Bootstrapping Configuration

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer 
    xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd"
    version="2.4.0">
  <scanDirectories>
    <source>
      <dir>src</dir>
      <dir>tests</dir>
    </source>
  </scanDirectories>
  <definitionProviders>
    <definitionProvider>ThirdPartyServicesProvider</definitionProvider>
  </definitionProviders>
</annotatedContainer>
```

## Step 5 - Bootstrap your Container

```php
<?php

use Psr\Log\LoggerInterface;
use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Event\Emitter;

$container = Bootstrap::fromAnnotatedContainerConventions(
    new YourContainerFactory(),
    new Emitter()
)->bootstrapContainer();
```

Now, your PSR Logger will be created through a factory. Any services can inject a `LoggerInterface` directly in the constructor, preferred, or implement the `LoggerAwareInterface` to have it injected automatically after construction.