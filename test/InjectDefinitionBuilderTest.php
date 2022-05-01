<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\objectType;
use function Cspray\Typiphy\stringType;

class InjectDefinitionBuilderTest extends TestCase {

    public function testInjectDefinitionWithNoMethodOrPropertyThrowsException() {
        $builder = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class));

        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('A method or property to inject into MUST be provided before building an InjectDefinition.');
        $builder->build();
    }

    public function testInjectDefinitionWithMethodAndPropertyThrowsException() {
        $builder = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class));

        $builder = $builder->withMethod('does-not-matter', stringType(), 'else')->withProperty(stringType(), 'else');

        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('A method and property MUST NOT be set together when building an InjectDefinition.');
        $builder->build();
    }

    public function testInjectDefinitionWithoutValueThrowsException() {
        $builder = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class));

        $builder = $builder->withMethod('does-not-matter', stringType(), 'else');

        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('A value MUST be provided when building an InjectDefinition.');
        $builder->build();
    }

    public function testInjectDefinitionWithMethodHasDifferentObject() {
        $builder = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class));

        $this->assertNotSame($builder, $builder->withMethod('foo', stringType(), 'baz'));
    }

    public function testInjectDefinitionWithPropertyHasDifferentObject() {
        $builder = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class));

        $this->assertNotSame($builder, $builder->withProperty(stringType(), 'bar'));
    }

    public function testInjectDefinitionWithValueHasDifferentObject() {
        $builder = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class));

        $this->assertNotSame($builder, $builder->withValue('foo'));
    }

    public function testInjectDefinitionWithStoreHasDifferentObject() {
        $builder = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class));

        $this->assertNotSame($builder, $builder->withStore('foo-store'));
    }

    public function testInjectDefinitionWithProfilesHasDifferentObject() {
        $builder = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class));

        $this->assertNotSame($builder, $builder->withProfiles('profile'));
    }

    public function testValidInjectDefinitionGetService() {
        $injectDefinition = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class))
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame(objectType(DummyApps\SimpleServices\FooImplementation::class), $injectDefinition->getService());
    }

    public function testValidParameterInjectDefinitionGetTargetIdentifierIsMethod() {
        $injectDefinition = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class))
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertTrue($injectDefinition->getTargetIdentifier()->isMethodParameter());
    }

    public function testValidParameterInjectDefinitionGetTargetIdentifierIsProperty() {
        $injectDefinition = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class))
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertFalse($injectDefinition->getTargetIdentifier()->isClassProperty());
    }

    public function testValidParameterInjectDefinitionGetTargetIdentifierGetName() {
        $injectDefinition = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class))
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame('paramName', $injectDefinition->getTargetIdentifier()->getName());
    }

    public function testValidInjectDefinitionTargetIdentifierGetValue() {
        $injectDefinition = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class))
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame('methodName', $injectDefinition->getTargetIdentifier()->getMethodName());
    }

    public function testValidInjectDefinitionTargetIdentifierGetClass() {
        $injectDefinition = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class))
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame(objectType(DummyApps\SimpleServices\FooImplementation::class), $injectDefinition->getTargetIdentifier()->getClass());
    }

    public function testValidInjectDefinitionGetType() {
        $injectDefinition = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class))
            ->withMethod('methodName', $expectedType = stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame($expectedType, $injectDefinition->getType());
    }

    public function testValidInjectDefinitionGetValue() {
        $injectDefinition = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class))
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame('foobar', $injectDefinition->getValue());
    }

    public function testValidInjectDefinitionWithNoProfilesGetProfiles() {
        $injectDefinition = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class))
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertEmpty($injectDefinition->getProfiles());
    }

    public function testValidInjectDefinitionWithOneProfileGetProfiles() {
        $injectDefinition = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class))
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->withProfiles('foo')
            ->build();

        $this->assertSame(['foo'], $injectDefinition->getProfiles());
    }

    public function testValidInjectDefinitionWithAdditionalProfilesGetProfiles() {
        $injectDefinition = InjectDefinitionBuilder::forService(objectType(DummyApps\SimpleServices\FooImplementation::class))
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->withProfiles('foo', 'bar', 'baz')
            ->build();

        $this->assertSame(['foo', 'bar', 'baz'], $injectDefinition->getProfiles());
    }

}