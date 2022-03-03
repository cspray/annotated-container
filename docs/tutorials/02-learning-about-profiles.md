# Learning About Profiles

Profiles are an important part of AnnotatedContainer. They provide a way to narrowly specify which concrete services are 
available for a given environment or specific runtime of your application. All Services always have a profile; if none 
has been specified, they belong to the 'default' profile. When a `Container` is compiled specific profiles, 1 or more, are 
provided and only services matching those profiles will be available.

Let's use an example that's a little more realistic and something you might have encountered in your professional 
programming career. We've been tasked with creating a `BlobStorageService` that will store some binary data on a cloud 
storage when running in production and a local filesystem when running in development. Let's assume that we have the 
following interfaces and classes defined:

```php
<?php

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface BlobStorageService {

    public function get(string $path) : string;

    public function put(string $path, string $contents) : void;

}

#[Service]
class CloudStorageService implements BlobStorageService {

    public function get(string $path) : string {
        return someCloudProviderMethod($path);
    }
    
    public function put(string $path, string $contents) : void {
        someCloudProviderMethod($path, $contents);
    }

}

#[Service]
class FileStorageService implements BlobStorageService {

    public function get(string $path) : string {
        return file_get_contents($path);
    }
    
    public function put(string $path, string $contents) : void {
        file_put_contents($path, $contents);
    }

}
```

While there may be some concerns with the error handling of these implementations they'll get the job done! The only 
problem is that we don't know which `BlobStorageService` implementation to use. If we attempted to create a Container 
with these annotations when we called `ContainerInterface::get(BlobStorageService::class)` we'd get an error because 
we can't figure out which concrete service should be used for the given abstract service. Defining the profile for 
each service and adjusting the profiles for the Container compiling will solve this problem!

## Defining the ServiceProfile

There is a `#[ServiceProfile]` Attribute that can be annotated on the class-level of a Service to specify which specific 
profiles, 1 or more, a Service belongs to. In our case we'll define two profiles; `dev` and `cloud-provider`. We'd add 
the following Attributes to our source code:

```php
<?php

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceProfile;

// ... rest of file remains untouched

#[Service]
#[ServiceProfile(['cloud-provider'])]
class CloudStorageService implements BlobStorageService {

    // ... rest of class remains untouched
    
}

#[Service]
#[ServiceProfile(['dev'])]
class FileStorageService implements BlobStorageService {

    // ... rest of class remains untouched

}
```

Now if we specify the `cloud-provider` profile we'll get `CloudStorageService` from `ContainerInterface::get(BlobStorageService)`. 
Conversely if we specify the `dev` profile we'll get `FileStorageService` from `ContainerInterface::get(BlobStorageService)`. 
As long as we specified 1 or the other profile when compiling our Container we'll know which concrete Service to use.

Nifty!

## Configuring Profiles for the Container

Now that we have our services properly annotated it is important to construct the correct `ContainerDefinitionCompileOptions`.
If we were on the cloud provider we'd want to build the options with the following code:

```php
<?php

use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;

$compileOptions == ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/src')
    ->withProfiles('default', 'cloud-provider')
    ->build();
```

If we were on our local machine we'd want to build the options with the following code:

```php
<?php

use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;

$compileOptions == ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/src')
    ->withProfiles('default', 'dev')
    ->build();
```

It is important...**always include the 'default' profile**! Many services will not be annotated with a specific profile 
and will be implicitly added to the 'default' profile. Failure to include it in the list of active profiles will likely 
make the vast majority of services misconfigured or unavailable.

## Profiles != Environments

It is important to keep in mind that AnnotatedContainer profiles are _not_ a 1-for-1 mapping to environments. A third 
`BlobStorageService` implementation might come along for a different cloud-provider for a client that runs your app 
on their own hardware. In this instance they'd both be considered a "production" environment, perhaps, but that wouldn't 
be enough to distinguish which service to use. If it helps, think of Profiles as a way to tag and identify certain attributes 
of an environment that helps determine which service might be used.
