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