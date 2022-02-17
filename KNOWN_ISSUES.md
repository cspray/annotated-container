# Known Issues

Parsing and resolving dependencies can be a tricky task. We try to make some sane opinions and 
judgement calls when resolving what should be shared and aliased based on the annotation you use.
However, there are scenarios that are not currently safeguarded against in the codebase that are 
known to cause problems or are very likely to cause problem. Many, if not all, of these issues are 
planned to be resolved in some way during work on the 0.3.x release. Until then if you're using the 
library please be aware of these "gotchas"!

## Invalid Scalar and Service Types

A relatively minor problem that represents a misconfiguration, it is possible to define a scalar value with 
a type that's not valid for the parameter it is annotating. Take the following example:

```php
<?php

use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class Foo {

    private string $bar;

    public function __construct(
        #[InjectScalar(42)]
        string $bar
    ) {
        $this->bar = $bar;
    }

}
```

In the above example there's a pretty glaring error that when we attempt to construct `Foo` an `int` value will be 
passed to a `string`. Currently, the behavior of this library would to fail at runtime when the object is constructed
with a `TypeError`. The same problem can exist in `UseService` if you pass a type that doesn't implement the 
declaration. In the 0.3 push we'll be throwing an exception on invalid types in `Define*` Attributes during the 
compile step.

### Workaround

The workaround here is pretty simple... configure the correct type of value for the given scalar parameter!

## Multiple Scalar Definitions

There are currently multiple ways to define a scalar value on a param; with either the `UseScalar` or 
`UseScalarFromEnv` Attributes. Take the following example:

```php
<?php

use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\InjectEnv;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class Foo {

    public function __construct(
        #[InjectScalar('user')]
        #[InjectEnv('USER')]
        private string $user
    ) {}

}
```

In the above example there's 2 definitions for the scalar value of `$user`, which one should we use? 
There's no reliable way to determine this and having both of these annotations on the same parameter 
is a logical error. Currently, the behavior of the library is undefined in this scenario. What is 
likely going to happen is that the last Attribute parsed will "win" and that value will be set. In the
0.3 push we'll be throwing an exception when you attempt to define a scalar value with more than 1 
parameter.

### Workaround

The workaround here is pretty simple... don't define multiple scalar values on the same parameter!

## Orphaned Attributes

It's possible that any of the Attributes could be put onto a class or interface not marked as a Service. 
Take the following example:

```php
<?php

use Cspray\AnnotatedContainer\Attribute\InjectScalar;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

class Foo {

    public function __construct(
        #[InjectScalar('cspray')]
        public string $user
    ) {}

    #[ServicePrepare]
    public function setUp() {}
}
```

In the above example the 2 Attributes imply that this class is meant to be autowired. However, with the 
missing `#[Service]` Attribute on the class no functionality would be enabled for this type. It would not
be shared nor would it be used as an alias. In the 0.3 push we'll be throwing an exception when we encounter 
an orphaned Attribute.

### Workaround

The workaround here is to ensure that the `#[Service]` Attribute is properly applied to all interfaces and 
objects that should be managed by the Injector.