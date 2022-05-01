## Calling Post Construct Methods

Sometimes you might not need the full power, and complexity, of having a service factory, but you do need to have some method called after the object is constructed. AnnotatedContainer can help you _prepare_ your Services by automatically calling these methods and injecting services and values into them.

We're going to use the same example from `/docs/tutorials/01-getting-started.md` and change from using constructor injection to setter injection. For clarity, here's the code from that tutorial with some slight modifications to change injection types.

```php
<?php

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

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
    private BlobStorageEventEmitter $emitter;
    
    public function store(string $identifier, string $contents) : void {
        file_put_contents($identifier, $contents);
        $this->emitter->emitStoreEvent($identifier);
    }
    
    public function retrieve(string $identifier) : ?string {
        $contents = file_get_contents($identifier) ?? null;
        $this->emitter->emitRetrieveEvent($identifier);
        return $contents;
    }
    
    #[ServicePrepare]
    public function setEmitter(BlobStorageEventEmitter $emitter) {
        $this->emitter = $emitter;
    }

}
```

Note the new method, `FilesystemStorage::setEmitter`. It has an Attribute on it called `#[ServicePrepare]`; this tells AnnotatedContainer that you want to invoke this method immediately after construction. Also note that we declared the`BlobStorageEventEmitter` service as a dependency. Any service or value the container can inject you can declare in your method's arguments.