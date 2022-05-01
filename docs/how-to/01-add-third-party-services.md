# Adding Third-Party Services

It is very likely that you'll need to add some service to the Container that can't be annotated. AnnotatedContainer offers a set of functions to easily add third-party services with all the feature-parity and functionality available to annotated code. This guide goes through a step-by-step guide on how to integrate the popular [Monolog](https://github.com/Seldaek/monolog) library with [PSR-3](https://www.php-fig.org/psr/psr-3/) services. 

> This guide assumes a basic understanding on how to interact with this library. If you're unsure of something we discuss here it is recommended you checkout the rest of the /docs/tutorials section.

## Step 1 - Install PSR-3 and Monolog

```shell
composer require monolog/monolog psr/log
```

## Step 2 - Create a Service Factory

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

## Step 3 - Define Compile Options to scan source

This code should likely be in your app's bootstrap or startup process.

```php
<?php

use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;

$compileOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/src')
    ->build();
```

## Step 4 - Update Compile Options to include 3rd-party Services

```php
<?php

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Cspray\AnnotatedContainer\CallableContainerDefinitionBuilderContextConsumer;
use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use function Cspray\AnnotatedContainer\service;
use function Cspray\AnnotatedContainer\serviceDelegate;
use function Cspray\Typiphy\objectType;

$compileOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/src')
    ->withContainerDefinitionBuilderContextConsumer(new CallableContainerDefinitionBuilderContextConsumer(function($context) {
        service($context, $loggerType = objectType(LoggerInterface::class));
        serviceDelegate($context, $loggerType, objectType(MonologLoggerFactory::class), 'createLogger');
    }))
    ->build();
```

## Step 5 - Compile your Container

```php
<?php

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Cspray\AnnotatedContainer\CallableContainerDefinitionBuilderContextConsumer;
use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use function Cspray\AnnotatedContainer\service;
use function Cspray\AnnotatedContainer\serviceDelegate;

$compileOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/src')
    ->withContainerDefinitionBuilderContextConsumer(new CallableContainerDefinitionBuilderContextConsumer(function($context) {
        service($context, $loggerType = objectType(LoggerInterface::class));
        serviceDelegate($context, $loggerType, objectType(MonologLoggerFactory::class), 'createLogger');
        servicePrepare();
    }))
    ->build();

$containerCompiler = \Cspray\AnnotatedContainer\ContainerDefinitionCompilerFactory::withoutCache()->getCompiler();
$containerDefinition = $containerCompiler->compile($compileOptions);

$container = (new \Cspray\AnnotatedContainer\AurynContainerFactory())->createContainer($containerDefinition);
```