# Autowire Aware Invoker

It is common in PHP to use callables for a wide variety of functionality. Annotated Container provides functionality to invoke a callable and recursively autowire the dependencies it requires from available services. The Container returned from a `ContainerFactory` is a [type intersect](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.composite.intersection) that includes the `Cspray\AnnotatedContainer\AutowireableInvoker`. You can depend on this type in your constructors to invoke autowired callables!

## Example

In our example we're going to create some callables that interact with `Widget` implementations. Some of those implementations depend on services from the Container, while other implementations depend on scalar values that must be provided when you create the object. Before we look at how to use the `AutowireableInvoker` let's take a look at an example codebase.

```php
<?php declare(strict_types=1);

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\AutowireableFactory;

#[Service]
interface SomeService {}

#[Service]
class FooService implements SomeService {}

#[Service]
class BarService {}

interface Widget {}

class FooWidget implements Widget {

    public function __construct(public readonly SomeService $service) {}

}

class BarWidget implements Widget {

    public function __construct(
        private readonly BarService $service, 
        private readonly string $foo
    ) {} 

}
```

Now, let's take a look at some callables that will consume dependencies from our example.


```php
<?php declare(strict_types=1);

use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use function Cspray\AnnotatedContainer\compiler;
use function Cspray\AnnotatedContainer\containerFactory;
use function Cspray\AnnotatedContainer\autowiredParams;
use function Cspray\AnnotatedContainer\rawParam;
use function Cspray\AnnotatedContainer\serviceParam;

$containerDefinition = compiler()->compile(
    ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/src')->build()
);

$autowiredInvoker = containerFactory()->createContainer($containerDefinition);

$someServiceConsumer = function(SomeService $service) {};
$widgetConsumer = function(Widget $widget) {};
$serviceAndScalarConsumer = function(SomeServce $service, int $meaning) {};

// Will be passed the FooService
$autowiredInvoker->invoke($someServiceConsumer); 

// Will be passed the FooWidget, the Container will automagically instantiate 
// an instance of FooWidget and inject its dependencies
$autowiredInvoker->invoke($widgetConsumer, autowiredParams(serviceParam('widget', FooWidget::class)));

// If the value can't be resolved by the Container you can define just its value
$autowiredInvoker->invoke($serviceAndScalarConsumer, autowiredParams(rawParam('meaning', 42)));
```
