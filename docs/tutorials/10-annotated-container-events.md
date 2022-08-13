# Annotated Container Events

Annotated Container has an event system that allows you to get access to pertinent information during [Annotated Container's lifecycle](../references/02-annotated-container-lifecycle.md). This system could be used to gather information about the compilation process, perform action on the Container post creation, or some other action that might be necessary. Below we'll talk about how to register listeners and perform actions.

## Event Overview

Every triggered event implements the `Cspray\AnnotatedContainer\AnnotatedContainerEvent` interface. This provides lifecycle phase currently processing and, if applicable, the `Cspray\AnnotatedContainer\ContainerDefinition` or `Cspray\AnnotatedContainer\AnnotatedContainer` based on the lifecycle phase.

## Registering Listeners

 First, you'll need to create a `Cspray\AnnotatedContainer\AnnotatedContainerListener` implementation. 

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\AnnotatedContainerListener;
use Cspray\AnnotatedContainer\AnnotatedContainerEvent;

final class MyContainerListener implements AnnotatedContainerListener {

    public function handle(AnnotatedContainerEvent $event) : void {
        // Do whatever your listener needs
        // This will be invoked for every lifecycle event that is triggered
        // Check the $event->getLifecycle() if you only care about specific events 
    }

}
```

If you're using the built-in [bootstrapping functionality](../how-to/02-bootstrap-your-container.md) or using the [functional API](../references/03-functional-api.md) to compile and create your Container then the next step is straight-forward. You'll need to make sure the event emitter knows about your listener.

This code should live in your app's bootstrapping code, before you call `Cspray\AnnotatedContainer\Bootstrap::bootstrapContainer()`.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap;
use function Cspray\AnnotatedContainer\eventEmitter;

evetEmitter()->registerListener(new MyContainerListener());

$container = (new Bootstrap())->bootstrapContainer();
```

Now your listener will be triggered on every event emitted by Annotated Container!

If you aren't using the built-in bootstrapping or functional API be sure to call `EventEmitter::registerListener` on the implementations that you create.