## Injecting Scalar Values

There's a good chance there's some object or component of your application that requires scalar values to work. As much 
as we might add layers of abstraction some things just boil down to a good ol' string or int. Since we can't rightly inject 
a service for these values there are other mechanisms for injecting non-object values into your services.

In this example we're going to create a `DatabaseConfiguration` object; these types of objects are ubiquitous, easy to 
relate to, and requires scalar values. Let's take a look at some code!

### Injecting Hard-coded Values

```php
<?php

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\InjectScalar;

class DatabaseConfiguration {

    public function __construct(
        #[InjectScalar('localhost')] private string $host,
        #[InjectScalar(5432)] private int $port 
    ) {}

    public function getHost() : string {
        return $this->host;
    } 
    
    public function getPort() : int {
        return $this->port;
    }

}
```

In our example we still require values in our constructor like a normal dependency, but we also annotate it with an 
Attribute called `#[InjectScalar]`. As the name suggests this Attribute will cause the Container to inject whatever 
value is present into the corresponding parameter. This works fine if you know what the value is ahead of time, but you 
might need to change the value based on what environment you're running. We've got you covered!

### Injecting Environment Variables

```php
<?php

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\InjectEnv;
use Cspray\AnnotatedContainer\Attribute\InjectScalar;

class DatabaseConfiguration {

    public function __construct(
        #[InjectEnv('DATABASE_HOST')] private string $host,
        #[InjectScalar(5432)] private int $port 
    ) {}

    public function getHost() : string {
        return $this->host;
    } 
    
    public function getPort() : int {
        return $this->port;
    }

}
```

Notice we've changed the Attribute on our first parameter, `$host`, to `#[InjectEnv]`. This Attribute will cause the 
Container to inject whatever value is present for the given environment variable _at runtime_. This way you can have your 
development environment and testing environments use different databases with no changes to the code in order to do so.