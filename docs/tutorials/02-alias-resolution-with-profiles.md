# Alias Resolution with Profiles

Resolving aliases properly is an important part of any autowired dependency injection solution. In the code examples in the README there's only 1 concrete service, but it is common for multiple implementations to be available for an abstract service. In those types of situations AnnotatedContainer can't infer the appropriate service to use. You'll need to provide a little more configuration to solve this problem; profiles are a great way to deal with this issue.

Profiles are an important part of AnnotatedContainer. They provide a way to narrowly specify which services are available for a given runtime of your application. All Services always have at least 1 profile and can contain any number. Services implicitly belong to the 'default' profile if one hasn't been explicitly set. At time of static analysis active profiles are specified and only those services matching the profile are included in the container. 

We're going to expand on the example found in our README by adding a second concrete service. We'll then show how you could use profiles to specify which concrete service to use based on different runtime requirements. Assume we've been given requirements to create a new implementation that will store the blob on some cloud storage provider instead of the local filesystem. Here's our example from the README with our new implementation.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

// interfaces and classes in __DIR__ . '/src'

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\InjectEnv;
use Cspray\AnnotatedContainer\AurynContainerFactory;

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

Now that we have the 2nd service implemented we'll encounter a problem whenever we construct a service that's type-hinted`BlobStorage` or when `ContainerInterface::get(BlobStorage::class)` is called. Which concrete implementation to use in this situation? If we specify a profile for each concrete service the static analysis will be able to figure it out!

## Defining the Service profiles

The `#[Service]` Attribute has an argument where you can provide which profiles, 1 or more, a Service belongs to. In our case we'll define two profiles; `local` and `cloud`. We'd make the following changes to our source code:

```php
<?php

use Cspray\AnnotatedContainer\Attribute\Service;

// ... rest of file remains untouched

#[Service(profiles: ['cloud'])]
class CloudStorage implements BlobStorage {

    // ... rest of class remains untouched
    
}

#[Service(profiles: ['local'])]
class FilesystemStorage implements BlobStorage {

    // ... rest of class remains untouched

}
```

Now if we specify the `cloud` profile we'll get `CloudStorage` from `ContainerInterface::get(BlobStorage)`. Conversely if we specify the `local` profile we'll get `FilesystemStorage` from `ContainerInterface::get(BlobStorage)`. As long as we specified 1 or the other profile when compiling our Container we'll know which concrete Service to use.

Nifty!

## Configuring Profiles for the Container

Now that we have our services properly annotated it is important to construct the correct `ContainerFactoryOptions`. If we were on the cloud provider we'd want to build the options with the following code:

```php
<?php

use Cspray\AnnotatedContainer\ContainerDefinitionFactoryOptionsBuilder;

$compileOptions == ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/src')
    ->withProfiles('default', 'cloud')
    ->build();
```

If we were on a local machine we'd want to build the options with the following code:

```php
<?php

use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;

$compileOptions == ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/src')
    ->withProfiles('default', 'local')
    ->build();
```

It is important...**always include the 'default' profile**! Many services will not be annotated with a specific profile 
and will be implicitly added to the 'default' profile. Failure to include it in the list of active profiles will likely 
make the vast majority of services configured incorrectly or unavailable.

## Profiles != Environments

It is important to keep in mind that AnnotatedContainer profiles are _not_ a 1-for-1 mapping to environments. Sometimes 
a profile name will make sense to match an environment name. Other times, it won't. Imagine a third `BlobStorage` implementation 
might come along for a different cloud-provider or a client that still uses FTP. In this instance they'd both be considered 
a "production" environment, perhaps, but that wouldn't be enough to distinguish which service to use. If it helps, think 
of Profiles as a way to tag and identify certain attributes of an environment that helps determine which service might be used.
