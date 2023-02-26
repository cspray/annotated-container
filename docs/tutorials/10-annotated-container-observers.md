# Annotated Container Observers

Annotated Container has a boostrapping observer system that allows you to get access to pertinent information during the creation of your container. This system could be used to gather information about the compilation process, perform action on the Container post creation, or some other action that might be necessary. Below we'll talk about how to take advantage of this system to bring more complex functionality to applications powered by Annotated Container.

## Registering Observers

 First, you'll need to decide on what type of observer you want to implement. The example will implement all possible interfaces, to show what's possible. You do not have to implement all 3 in your project; you can choose any of them to implement.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\ContainerCreatedObserver;
use Cspray\AnnotatedContainer\Bootstrap\PostAnalysisObserver;
use Cspray\AnnotatedContainer\Bootstrap\PreAnalysisObserver;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

final class MyContainerObserver implements PreAnalysisObserver, PostAnalysisObserver, ContainerCreatedObserver {

    public function notifyPreAnalysis(ActiveProfiles $profiles) : void {
        // do something before the source code is analyzed and the ContainerDefinition is compiled
    }

    public function notifyPostAnalysis(ActiveProfiles $profiles, ContainerDefinition $containerDefinition) : void {
        // do something after the source is analyzed and the ContainerDefinition is compiled
    }

    public function notifyContainerCreated(ActiveProfiles $profiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
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

