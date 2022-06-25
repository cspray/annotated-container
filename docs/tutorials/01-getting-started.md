# Getting Started

Thanks for choosing to learn more about Annotated Container! In this document we'll show you how to get started with the library by expanding on the example from the README and go step-by-step what's going on. After that we'll give you some places to go next based on what you might be interested in learning.

Our requirements have some external code executed when a blob is stored or retrieved. We decide to introduce a new abstract and concrete Service that will emit blob storage events. Our existing Service will become dependent on this new Service. Let's start digging into some code!

> The rest of this guide assumes you've installed a backing container library! Please check out the README Installation for more details.

## Abstract Services

```php
<?php

use Cspray\AnnotatedContainer\Attribute\Service;

// This is the Service that was defined in the README
#[Service]
interface BlobStorage {

    public function store(string $identifier, string $contents) : void;
    
    public function retrieve(string $identifier) : ?string;

}

// This is the new Service we're adding
#[Service]
interface BlobStorageEventEmitter {

    public function onStore(callable $listener) : void;
    
    public function onRetrieve(callable $listener) : void;
    
    public function emitStoreEvent(string $identifier) : void;
    
    public function emitRetrieveEvent(string $identifier) : void;

}
```

Here we have annotated 2 interfaces, `BlobStorage` and `BlobStorageEventEmitter`. When an interface or abstract class is annotated with the `#[Service]` attribute they are called _abstract services_. Abstract services cannot be instantiated, but you want to type-hint in constructors and "share" with the container. Sharing a service with the container ensures that 1 instance is created and shared wherever you might type-hint it or whenever you call `ContainerInterface::get()`. In other words, for our example, you can call `ContainerInterface::get(BlobStorage::class)` and get the same instance every time.

## Concrete Services

```php
<?php

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class StandardBlobStorageEventEmitter implements BlobStorageEventEmitter {

    private array $storeListeners = [];
    private array $retrieveListeners = [];

    public function onStore(callable $listener) : void {
        $this->storeListeners[] = $listener;
    }
    
    public function onRetrieve(callable $listener) : void {
        $this->retrieveListeners[] = $listener;
    }
    
    public function emitStoreEvent(string $identifier) : void {
        foreach ($this->storeListeners as $storeListener) {
            $storeListener($identifier);
        } 
    }
    
    public function emitRetrieveEvent(string $identifier) : void {
        foreach ($this->retrieveListeners as $retrieveListener) {
            $retrieveListener($identifier);
        }
    }

}

#[Service]
class FilesystemStorage implements BlobStorage {

    public function __construct(private BlobStorageEventEmitter $emitter) {}
    
    public function store(string $identifier, string $contents) : void {
        file_put_contents($identifier, $contents);
        $this->emitter->emitStoreEvent($identifier);
    }
    
    public function retrieve(string $identifier) : ?string {
        $contents = file_get_contents($identifier) ?? null;
        $this->emitter->emitRetrieveEvent($identifier);
        return $contents;
    }

}
```

We also annotated 2 concrete classes, `BlobStorageEventEmitter` and `FilesystemStorage`, which are called _concrete services_. While it is not necessary for a concrete service to satisfy a contract from an abstract service it is expected that the Annotated Container will often be used in this manner. Because there's only 1 concrete service for each abstract service we know, for example, when `ContainerInterface::get(BlobStorage::class)`is called you really want to get an instance of`FilesystemStorage`.

When a concrete service is used to instantiate an abstract service it is referred to as an _alias_; an alias that is inferred from the static analysis of your codebase is considered _implicit_. It is possible for an abstract service to have multiple concrete services, and an alias can't be inferred. We'll talk about how to resolve those types of conflicts in a separate document.

The `FilesystemStorage` service has been refactored from our example in README.md by adding an event emitter. We added the`BlobStorageEventEmitter` type-hint to our constructor. Our Container is autowired and knows that you're looking for a Service to be injected. Since the `BlobStorageEventEmitter` has an implicit alias we don't need to specify which concrete implementation to use.

## Using our Services

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Cspray\AnnotatedContainer\ContainerDefinitionCompilerBuilder;
use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use function Cspray\AnnotatedContainer\compiler;
use function Cspray\AnnotatedContainer\containerFactory;

$containerDefinition = compiler()->compile(
    ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/src')->build()
);
$container = containerFactory()->createContainer($containerDefinition);

$emitter = $container->get(BlobStorageEventEmitter::class);
$emitter->onStore(fn(string $identifier) -> echo "Stored $identifier");

$storage = $container->get(BlobStorage::class);
$storage->store('foo.txt', 'bar');
```

A key aspect of AnnotatedContainer is how each Service has 1 instance associated with it. See in the calling code that uses the services we were able to call `ContainerInterface::get(BlobStorageEventEmitter::class)`. Changes to that object were present in the service injected into `FilesystemStorage`. All Services are shared and will have the same instance injected through autowiring and returned from `ContainerInterface::get()`.

## Wrapping Up

In this document we learned some important concepts in AnnotatedContainer; abstract and concrete services, aliasing abstract services, how to auto-wire service injection, and how all services are effectively singletons. This example is meant to show you some bread & butter aspects of AnnotatedContainer that we expect is the "regular" use-case. There's a lot more functionality available! We highly recommend you checking out the rest of the documents for more details.

## Next Steps

- [Resolve Aliases with Multiple Profiles](./02-alias-resolution-with-profiles.md)
- [Resolve Aliases with Attributes](./03-alias-resolution-with-attributes.md)
- [Using Factory to Create Services](./04-using-service-factories.md)
- [Invoking Methods Post-Construct](./05-calling-post-construct-methods.md)
- [Injecting Non-Service Values](./06-injecting-scalar-values.md)
- [Using Autowire Aware Factory](./08-autowire-aware-factory.md)
- [Using Autowire Aware Invoker](./09-autowire-aware-invoker.md)