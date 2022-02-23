<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\SimpleServices;
use Cspray\AnnotatedContainer\DummyApps\MultipleSimpleServices;
use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;
use PHPUnit\Framework\TestCase;

class AliasDefinitionBuilderTest extends TestCase {

    public function testAddingConcreteServiceDefinitionAsAbstractTypeThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooImplementation::class)->build();
        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('Attempted to assign concrete type ' . SimpleServices\FooImplementation::class . ' as an abstract alias.');
        AliasDefinitionBuilder::forAbstract($serviceDefinition);
    }

    public function testAddingAbstractServiceDefinitionAsConcreteTypeThrowsException() {
        $serviceDefinition1 = ServiceDefinitionBuilder::forAbstract(SimpleServices\FooInterface::class)->build();
        $serviceDefinition2 = ServiceDefinitionBuilder::forAbstract(MultipleSimpleServices\FooInterface::class)->build();
        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('Attempted to assign abstract type ' . MultipleSimpleServices\FooInterface::class . ' as a concrete alias.');
        AliasDefinitionBuilder::forAbstract($serviceDefinition1)->withConcrete($serviceDefinition2);
    }

    public function testWithConcreteImmutableBuilder() {
        $abstract = ServiceDefinitionBuilder::forAbstract(SimpleServices\FooInterface::class)->build();
        $concrete = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooImplementation::class)->withImplementedService($abstract)->build();

        $builder1 = AliasDefinitionBuilder::forAbstract($abstract);
        $builder2 = $builder1->withConcrete($concrete);

        $this->assertNotSame($builder1, $builder2);
    }

    public function testWithConcreteReturnsCorrectServiceDefinitions() {
        $abstract = ServiceDefinitionBuilder::forAbstract(SimpleServices\FooInterface::class)->build();
        $concrete = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooImplementation::class)->withImplementedService($abstract)->build();

        $aliasDefinition = AliasDefinitionBuilder::forAbstract($abstract)->withConcrete($concrete)->build();

        $this->assertSame($abstract, $aliasDefinition->getAbstractService());
        $this->assertSame($concrete, $aliasDefinition->getConcreteService());
    }

}