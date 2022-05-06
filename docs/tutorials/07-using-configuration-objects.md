# Using Configuration Objects

AnnotatedContainer provides out-of-the-box functionality to allow defining type-safe configuration objects. Configurations are classes with public, readonly properties that can take advantage of all the features found in the `#[Inject]` Attribute. In the example below we'll take a look at a well-known configuration, the database config.

## Example Configuration

```php
<?php declare(strict_types=1);

namespace Acme;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;

#[Configuration]
final class DatabaseConfig {

    #[Inject('localhost', profiles: ['dev', 'test'])]
    #[Inject('DB_HOST', profiles: ['prod'], from: 'env')]
    public readonly string $host;

    #[Inject(5432)]
    public readonly int $port;

    #[Inject('postgres', profiles: ['dev', 'test'])]
    #[Inject('DB_USER', profiles: ['prod'], from: 'env')]
    public readonly string $user;

    #[Inject('', profiles: ['dev', 'test'])]
    #[Inject('DB_PASSWORD', profiles: ['prod'], from: 'env')]
    public readonly string $password;
    
    #[Inject('acme_dev', profiles: ['dev'])]
    #[Inject('acme_test', profiles: ['test'])]
    #[Inject('DB_NAME', profiles: ['prod'], from: 'env')]
    public readonly string $dbName;

}
```

Hopefully, all of the above makes sense to you! Either way, we'll take a walk-through each piece and explain what's going on.

## Walk-through

```php
#[Configuration]
final class DatabaseConfig {
```

Like the `#[Service]` Attribute annotating a class with `#[Configuration]` will cause that type to be shared with the Container. For our example, this means `Container::get(DatabaseConfig::class)` will return the same instance every time. Additionally, unlike the `#[Service]` Attribute the `#[Configuration]` Attribute will allow any properties you annotate with `#[Inject]` to be populated with the corresponding values.

```php
#[Inject('localhost', profiles: ['dev', 'test'])]
#[Inject('DB_HOST', profiles: ['prod'], from: 'env')]
public readonly string $host;
```

For the `$host` property, when the active profiles include 'dev' or 'test' the value `localhost` will be set on the property. When the profiles include 'prod' the value will be set from whatever is stored in the environment variable `DB_HOST`.

```php
#[Inject(5432)]
public readonly int $port;
```

For the `$port` property, we'll always inject the `5432` value. This will be set as long as you include the 'default' profile.

```php
#[Inject('postgres', profiles: ['dev', 'test'])]
#[Inject('DB_USER', profiles: ['prod'], from: 'env')]
public readonly string $user;
```

For the `$user` property, when the active profile list includes 'dev' or 'test' the value `postgres` will be set on the property. When the profiles include 'prod' the value will be set from whatever is stored in the environment variable `DB_USER`.

```php
#[Inject('', profiles: ['dev', 'test'])]
#[Inject('DB_PASSWORD', profiles: ['prod'], from: 'env')]
public readonly string $password;
```

For the `$password` property, when the active profile list includes 'dev' or 'test' the value ` ` will be set on the property. When the profiles include 'prod' the value will be set from whatever is stored in the environment variable `DB_PASSWORD`.

```php
#[Inject('acme_dev', profiles: ['dev'])]
#[Inject('acme_test', profiles: ['test'])]
#[Inject('DB_NAME', profiles: ['prod'], from: 'env')]
public readonly string $dbName;
```

For the `$dbName` property, when the active profile includes 'dev' the value `acme_dev` will be set on the property. When the active profile includes 'test' the value `acme_test` will be set on the property. When the profile  includes 'prod' the value will be set from whatever is stored in the environment variable `DB_NAME`.

## Caveats

While Configuration objects can be very useful there are a couple rules that need to be followed when working with them.

1. Configurations are concrete classes and never abstract or an interface. 
2. Configurations are not used to satisfy a service alias.
3. Properties should be `public readonly`.