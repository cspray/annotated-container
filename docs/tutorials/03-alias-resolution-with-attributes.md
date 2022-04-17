# Alias Resolution with Attributes

Resolving aliases properly is an important part of any autowired dependency injection solution. In the code examples in
the README there's only 1 concrete service, but it is common for multiple implementations to be available for an abstract
service. In those types of situations AnnotatedContainer can't infer the appropriate service to use. You'll need to provide
a little more configuration to solve this problem; explicitly defining which service to inject might be an appropriate 
solution.

Normally it's recommended that you use Profiles to specify which aliases to use. Sometimes that might not be a viable 
solution. Instead, you can specify exactly which service to use where you type-hint the Service in your constructors and 
container-executed methods. We'll use the same code from `/docs/tutorials/02-alias-resolution-with-profiles.md` and show 
a different way of resolving the alias.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

// interfaces and classes in __DIR__ . '/src'

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface BlobStorage {

    public function store(string $identifier, string $contents) : void;
    
    public function retrieve(string $identifier) : ?string;

}

#[Service]
class FilesystemStorage implements BlobStorage {
    
    public function store(string $identifier, string $contents) : void {
        file_put_contents($identifier, $contents);
    }
    
    public function retrieve(string $identifier) : ?string {
        return file_get_contents($identifier) ?? null;
    }

}

#[Service]
class CloudStorage implements BlobStorage {

    public function store(string $identifier, string $contents) : void {
        putOnCloud($identifier, $contents);
    }
    
    public function retrieve(string $identifier) : ?string {
        return getFromCloud($identifier);
    }

}
```

Now, if we were to create a Container and call `ContainerInterface::get(BlobStorage::class)` we'd get an exception thrown 
because we don't have a proper alias resolved. Since we have to define our attribute on the parameter type-hinting the 
service there's no way to make `ContainerInterface::get(BlobStroage::class)` work. We have to create something that 
takes the abstract service as a dependency. In practice this should not be a huge concern; if this is a serious problem 
in your app it is a sign that you're using the Container as a Service Locator and that's not something we encourage you 
to do.

## Consuming Code 

Since we have to have something that uses different `BlobStorage` let's create 2 concrete services. Each one will inject
a different `BlobStorage` implementation.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\InjectService;

#[Service]
class LocalConsumer {

    public function __construct(
        #[InjectService(FilesystemStorage::class)] private BlobStorage $blobStorage
    ) {}

    public function doSomething() {
        var_dump($this->blobStorage::class);
    }

}

#[Service]
class CloudConsumer {

    public function __construct(
        #[InjectService(CloudStorage::class)] private BlobStorage $blobStorage
    ) {}

    public function doSomething() {
        var_dump($this->blobStorage::class);
    }

}
```

In our example our 2 new services, `LocalConsumer` and `CloudConsumer`, are requiring a `BlobStorage` in their constructor. 
They've also added an Attribute to that parameter called `#[InjectService]`. The concrete service that you provide will 
be the one injected when the consuming service is constructed. As long as each place you type-hint `BlobStorage` has an 
Attribute defining which concrete implementation to use all your services will be able to be created properly. 

## Specifying a Primary Service

One of the drawbacks with using the `#[InjectService]` Attribute is that you have to specify it everywhere you might use the type-hint. Another is that you can't get the abstract service, in our example `ContainerInterface::get(BlobStorage::class)`, through the Container. With the v0.3 release it is now possible to mark a `#[Service]` as _primary_. If there are multiple possible aliases and one of them is marked as primary it'll be used as the alias for that service. For example, if we wanted to make `FilesystemStorage` the default alias we'd update the code to look like the following:

```php

<?php

require __DIR__ . '/vendor/autoload.php';

// interfaces and classes in __DIR__ . '/src'

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(primary: true)]
class FilesystemStorage implements BlobStorage {

    // ... The rest of the code from example
    
}
```
