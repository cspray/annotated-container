# Getting Started

Thanks for choosing to learn more about AnnotatedContainer! In this document we'll take a look at an example similar to 
the one found in the README. We'll add a little to it and then go over from a high-level what's happening. After that
we'll give you some places to go next based on what you might be interested in learning. First, let's take a look at our 
new code example.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

// interfaces and classes in __DIR__ . '/src'

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\InjectEnv;

#[Service]
interface Foo {

    public function getValue() : string;

}

#[Service]
interface Bar {

    public function getDecoratedValue(string $value) : string;

}

#[Service]
class BarImplementation implements Bar {

    public function getDecoratedValue(string $value) : string {
        return $value . ' got barred';
    }

}

#[Service]
class FooImplementation implements Foo {

    public function __construct(
        private Bar $decorator,
        #[InjectEnv('FOO_VALUE')] private string $value
    ) {}
    
    public function getValue() : string {
        return $this->decorator->getDecoratedValue($this->value);
    }

}

// app bootstrap in __DIR__ . '/app.php'

// This could be set by whatever system you have in place for environment variables. Here to show 
// that an env var is present
putenv('FOO_VALUE=foo');

use Cspray\AnnotatedContainer\AurynContainerFactory;
use Cspray\AnnotatedContainer\PhpParserInjectorDefinitionCompiler;
use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;

$compiler = new PhpParserInjectorDefinitionCompiler();
$containerDefinition = $compiler->compile(
    ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/src')->withProfiles('default')->build()
);
$container = (new AurynInjectorFactory)->createContainer($injectorDefinition);

var_dump($container->get(Foo::class)->getValue()); // 'foo got barred'
```

Though simple I hope it exposes some powers and capabilities possible with the AnnotatedContainer! Now, let's dig 
deeper!

## We annotated abstract services

In our example above we annotated two interfaces, `Foo` and `Bar`, which we call _abstract services_. These are interfaces, 
or abstract classes, that cannot be instantiated, but you want to type-hint in constructors and "share" with the container. 
Sharing a service with the container ensures that 1 instance is created and shared wherever you might type-hint it or 
whenever you call `ContainerInterface::get()`. In other words, you can call `ContainerInterface::get(Foo::class)` and get 
the same instance of `FooImplementation` every time.

## We annotated concrete services

In our example above we also annotated two concrete classes, `FooImplementation` and `BarImplementation`, which we call 
_concrete services_. These are classes that can be instantiated. While it is not necessary for a concrete service to 
satisfy a contract from an abstract service it is expected that the AnnotatedContainer will often be used 
in this manner. Because there's only 1 concrete service for each abstract service we know when `ContainerInterface::get(Foo::class)` 
is called you _really_ want to get an instance of `FooImplementation`.

It is possible for an abstract service to have multiple concrete services defined for it. We'll talk about how to resolve 
those type of conflicts in a separate document.

## We inject another Service in our constructor

## We injected an environment variable in our constructor

One of our concrete services required a scalar value, a string. No container can "construct" a string... no such concept 
exists in PHP! Instead, we tell AnnotatedContainer to use the value of the environment variable `FOO_VALUE`, as it is defined 
at runtime, to populate the value. While we inject an example value it could be imagined that this is injecting a database 
connection string or some other important value that might vary from environment to environment.

## Next Steps

There's still a lot of functionality to go over and learn about! For now, we highly recommend you check out `/docs/tutorials/02-learning-about-profiles.md`.
Profiles are a critically important aspect of AnnotatedContainer and to realize the full power of the container you 
must understand profiles. If you already know all there is to know about AnnotatedContainer profiles check out 
`/docs/tutorials/03-using-service-factory.md`, or `/docs/tutorials/04-calling-post-construct-methods.md`.
I also recommended checking out the `/docs/references` directory for more technical-heavy documentation including a 
list and description of all possible Attributes.