# Annotated Container Events

In Annotated Container v3, an event system was introduced that allows responding to virtually every part of the
Annotated Container lifecycle. These events provide a type-safe mechanism to respond to anything this library does 
with a rich set of contextual data. The rest of this document details how you can implement a `Listener` and ensure 
it is registered with the event emitter. For more information about what listeners are available checkout the 
[Event Listeners Reference](../references/03-event-listeners.md).

## Implementing Listeners

Implementing an interface that extends `Cspray\AnnotatedContainer\Event\Listener` will allow you to respond when 
certain things happen in the Annotated Container lifecycle. Each listener is specific to a certain event and provides 
one or more objects that detail Annotated Container's state at that point. For this example, we'll implement the
`Cspray\AnnotatedContainer\Event\Listener\Bootstrap\BeforeBootstrap` listener and require that a cache be provided. 
Other listeners are similar in nature, for a complete list check out [Event Listeners Reference](../references/03-event-listeners.md).

```php
<?php declare(strict_types=1);

use Cspray\AnnotatedContainer\Event\Listener\Bootstrap\BeforeBootstrap;

final readonly class RequireContainerDefinitionCache implements BeforeBootstrap {

    public function handleBeforeBootstrap(BootstrappingConfiguration $bootstrappingConfiguration) : void {
        if ($bootstrappingConfiguration->cache() === null) {
            throw new RuntimeException('A container definition cache must be provided with bootstrapping configuration');
        }
    }

}
```

Now that we have a listener implemented we need to make sure it is added to the Emitter to be invoked at the appropriate 
time. You can do this through your configuration or by programmatically constructing the Listener and adding it to the 
Emitter. Check out the [How To: Bootstrap Your Container](../how-to/02-bootstrap-your-container.md) for more information 
on how to register your listener.


