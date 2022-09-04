# Autowire Aware Factory

Sometimes you might want to take advantage of the autowiring capabilities of Annotated Container when you're creating an object that isn't a Service. The Container returned from a `ContainerFactory` is a [type intersect](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.composite.intersection) that includes the `Cspray\AnnotatedContainer\AutowireableFactory` interface. You can depend on this type in your factory constructors to create autowired objects!

## Example

In our example we're going to create a `WidgetFactory` that creates `Widget` implementations. Some of those implementations depend on services from the Container, while other implementations depend on scalar values that must be provided when you create the object. Before we look at how to use the `AutowireableFactory` let's take a look at an example codebase.

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

A couple important things to note. First, the Widget is not marked as a service, nor are any of its implementations. If you were to call `ContainerInterface::get(FooWidget::class)` it would fail as the service couldn't be found. Second, our Widgets depend on abstract services, concrete services, and values that can't be derived by the container.  Now, let's actually use the `AutowireableFactory` to create some Widgets!

```php
<?php declare(strict_types=1);

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use function Cspray\AnnotatedContainer\autowiredParams;
use function Cspray\AnnotatedContainer\rawParam;

$autowiredFactory = (new Bootstrap())->bootstrapContainer();

$fooWidget = $autowiredFactory->make(FooWidget::class);
$fooWidget->service instanceof FooService; // true

$barWidget = $autowiredFactory->make(BarWidget::class, autowiredParams(rawParam('foo', 'bar')));
$barWidget->service instanceof BarService; // true
$barWidget->foo === 'bar'; // true
```

## Making Defined Services

It should be noted that using the `AutowireableFactory` interface to create defined Services is not recommended. Making a shared service won't recreate the instance the way you'd might expect. Which means if you call `AutowireableFactory::make` and attempt to override the parameters that are used it will still use what is defined in the Container. If you have defined the class in the Container you should use the `ContainerInterface::get()` method to retrieve it.


