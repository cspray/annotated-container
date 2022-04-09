<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\SimpleUseScalar;
use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;
use PHPUnit\Framework\TestCase;

class InjectScalarDefinitionBuilderTest extends TestCase {

    public function testEmptyMethodNameThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseScalar\FooImplementation::class)->build();
        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('The method for an InjectScalarDefinition must not be blank.');
        InjectScalarDefinitionBuilder::forMethod($serviceDefinition, '');
    }

    public function testEmptyParamNameThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseScalar\FooImplementation::class)->build();
        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('The param name for an InjectScalarDefinition must not be blank.');
        InjectScalarDefinitionBuilder::forMethod($serviceDefinition, '__construct')->withParam(ScalarType::String, '');
    }

    public function testWithParamImmutableBuilder() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseScalar\FooImplementation::class)->build();
        $builder1 = InjectScalarDefinitionBuilder::forMethod($serviceDefinition, '__construct');
        $builder2 = $builder1->withParam(ScalarType::String, 'stringParam');

        $this->assertNotSame($builder1, $builder2);
    }

    public function testWithValueImmutableBuilder() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseScalar\FooImplementation::class)->build();
        $builder1 = InjectScalarDefinitionBuilder::forMethod($serviceDefinition, '__construct');
        $builder2 = $builder1->withValue(scalarValue('something'));

        $this->assertNotSame($builder1, $builder2);
    }

    public function testBuildWithoutParamThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseScalar\FooImplementation::class)->build();
        $builder = InjectScalarDefinitionBuilder::forMethod($serviceDefinition, '__construct');
        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('An InjectScalarDefinitionBuilder must have a parameter defined before building.');
        $builder->build();
    }

    public function testBuildWithoutValueThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseScalar\FooImplementation::class)->build();
        $builder = InjectScalarDefinitionBuilder::forMethod($serviceDefinition, '__construct')->withParam(ScalarType::String, 'stringParam');
        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('An InjectScalarDefinitionBuilder must have a parameter value defined before building.');
        $builder->build();
    }

    public function testBuildReturnsAppropriateService() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseScalar\FooImplementation::class)->build();
        $injectScalarDefinition = InjectScalarDefinitionBuilder::forMethod($serviceDefinition, '__construct')
            ->withParam(ScalarType::String, 'stringParam')
            ->withValue(scalarValue('string param value'))
            ->build();

        $this->assertSame($serviceDefinition, $injectScalarDefinition->getService());
    }

    public function testBuildReturnsAppropriateMethod() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseScalar\FooImplementation::class)->build();
        $injectScalarDefinition = InjectScalarDefinitionBuilder::forMethod($serviceDefinition, '__construct')
            ->withParam(ScalarType::String, 'stringParam')
            ->withValue(scalarValue('string param value'))
            ->build();

        $this->assertSame('__construct', $injectScalarDefinition->getMethod());
    }

    public function testBuildReturnsAppropriateParamType() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseScalar\FooImplementation::class)->build();
        $injectScalarDefinition = InjectScalarDefinitionBuilder::forMethod($serviceDefinition, '__construct')
            ->withParam(ScalarType::String, 'stringParam')
            ->withValue(scalarValue('string param value'))
            ->build();

        $this->assertSame(ScalarType::String, $injectScalarDefinition->getParamType());
    }

    public function testBuildReturnsAppropriateParamName() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseScalar\FooImplementation::class)->build();
        $injectScalarDefinition = InjectScalarDefinitionBuilder::forMethod($serviceDefinition, '__construct')
            ->withParam(ScalarType::String, 'stringParam')
            ->withValue(scalarValue('string param value'))
            ->build();

        $this->assertSame('stringParam', $injectScalarDefinition->getParamName());
    }

    public function testBuildReturnsAppropriateParamValue() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleUseScalar\FooImplementation::class)->build();
        $injectScalarDefinition = InjectScalarDefinitionBuilder::forMethod($serviceDefinition, '__construct')
            ->withParam(ScalarType::String, 'stringParam')
            ->withValue(scalarValue('string param value'))
            ->build();

        $this->assertSame('string param value', $injectScalarDefinition->getValue()->getCompileValue());
    }

}