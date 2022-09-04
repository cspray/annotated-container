# Annotated Container Observers

Annotated Container has a boostrapping observer system that allows you to get access to pertinent information during [Annotated Container's lifecycle](../references/02-annotated-container-lifecycle.md). This system could be used to gather information about the compilation process, perform action on the Container post creation, or some other action that might be necessary. Below we'll talk about how to register listeners and perform actions.

## Registering Observers

 First, you'll need to create a `Cspray\AnnotatedContainer\Boostrap\Observer` implementation. 

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\Observer;

final class MyContainerObserver implements Observer {

    public function beforeCompilation() : void {
    
    }

    public function afterCompilation(ContainerDefinition $containerDefinition) : void {
    
    }

    public function beforeContainerCreation(ContainerDefinition $containerDefinition) : void {
    
    }

    public function afterContainerCreation(ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
    
    }

}
```

This code should live in your app's bootstrapping code, before you call `Cspray\AnnotatedContainer\Bootstrap\Bootstrap::bootstrapContainer()`.

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