# Annotated Container Observers

Annotated Container has a boostrapping observer system that allows you to get access to pertinent information during [Annotated Container's lifecycle](../references/02-annotated-container-lifecycle.md). This system could be used to gather information about the compilation process, perform action on the Container post creation, or some other action that might be necessary. Below we'll talk about how to register listeners and perform actions.

## Registering Observers

 First, you'll need to create a `Cspray\AnnotatedContainer\Boostrap\Observer` implementation. 

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use Cspray\AnnotatedContainer\Bootstrap\Observer;

final class MyContainerObserver implements Observer {

    public function beforeCompilation(ActiveProfiles $profiles) : void {
        // do something before the source code is analyzed and the ContainerDefinition is compiled
    }

    public function afterCompilation(ActiveProfiles $profiles, ContainerDefinition $containerDefinition) : void {
        // do something after the source is analyzed and the ContainerDefinition is compiled
    }

    public function beforeContainerCreation(ActiveProfiles $profiles, ContainerDefinition $containerDefinition) : void {
        // do something after the ContainerDefinition is compiled, and the ConatinerFactory has been created with all 
        // configured ParameterStores
    }

    public function afterContainerCreation(ActiveProfiles $profiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        // do something after the AnnotatedContainer has been created based off of the given ContainerDefinition 
    }

}
```

This code should live in your app's bootstrapping code, before you call `Cspray\AnnotatedContainer\Bootstrap\Bootstrap::bootstrapContainer()`.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap;

$bootstrap = new Bootstrap();
$bootstrap->addObserver(new MyContainerObserver());
$container = $bootstrap->bootstrapContainer();
```

