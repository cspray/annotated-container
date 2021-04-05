# Known Issues

Parsing and resolving dependencies can be a tricky task. We try to make some sane opinions and 
judgement calls when resolving what should be shared and aliased based on the annotation you use.
However, there are scenarios that are not currently safeguarded against in the codebase that are 
known to cause problems or are very likely to cause problem. Many, if not all, of these issues are 
planned to be resolved in some way during work on the 0.3.x release. Until then if you're using the 
library please be aware of these "gotchas"!

## Multiple Alias Resolution

This is the "big" one that is planning to be resolved and/or mitigated in our 0.3 push. This problem 
boils down to having multiple Services that are meant to be aliased to a shared interface. Take this naive 
example in mind:

```php
<?php

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
interface Foo {}

#[Service]
class Bar implements Foo {}

#[Service]
class Baz implements Foo {}

#[Service]
class FooConsumer {

    public function __construct(Foo $foo) {}

}
```

Without explicitly defining which environment each `Foo` implementation should be used OR explicitly 
defining each declaration of `Foo` with `DefineService` this _should_ result in a conflict resolution error. 
When you construct `FooConsumer`, do we provide `Bar` or `Baz`? With no explicit configuration there's no 
way to safely know. Currently, the behavior of the library in the given scenario is undefined. The implementation 
passed to `Foo` in this case would be whichever implementation was parsed last and that might be different based 
on the OS specifics of filesystem iteration.

### Workarounds

The workarounds for this are to use the built-in features of the library to explicitly resolve these 
dependencies. You can check out the "Handling Multiple Alias Resolution" section of the README for more 
information about how to do this! In our 0.3 push we'll be throwing explicit Exceptions when we detect that 
multiple aliases could potentially resolve the same shared Service.

## Invalid Scalar Types

A relatively minor problem that represents a misconfiguration, it is possible to define a scalar value with 
a type that's not valid for the parameter it is annotating. Take the following example:

```php
<?php

use Cspray\AnnotatedInjector\Attribute\DefineScalar;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class Foo {

    public function __construct(
        #[DefineScalar(42)]
        public string $bar
    ) {}

}
```

In the above example there's a pretty glaring error that when we attempt to construct `Foo` an `int` value will be 
passed to a `string`. Currently, the behavior of this library would to fail at runtime when the object is constructed
with a `TypeError`. In the 0.3 push we'll be throwing an exception on invalid types in `DefineScalar` during the 
compile step.

### Workaround

The workaround here is pretty simple... configure the correct type of value for the given scalar parameter!

## Multiple Scalar Definitions

There are currently multiple ways to define a scalar value on a param; with either the `DefineScalar` or 
`DefineScalarFromEnv` Attributes. Take the following example:

```php
<?php

use Cspray\AnnotatedInjector\Attribute\DefineScalar;
use Cspray\AnnotatedInjector\Attribute\DefineScalarFromEnv;
use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class Foo {

    public function __construct(
        #[DefineScalar('user')]
        #[DefineScalarFromEnv('USER')]
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

use Cspray\AnnotatedInjector\Attribute\DefineScalar;
use Cspray\AnnotatedInjector\Attribute\ServicePrepare;

class Foo {

    public function __construct(
        #[DefineScalar('cspray')]
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