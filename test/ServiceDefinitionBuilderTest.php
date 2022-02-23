<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Cspray\AnnotatedContainer\DummyApps\SimpleServices;
use Cspray\AnnotatedContainer\DummyApps\MultipleImplementedServices;

class ServiceDefinitionBuilderTest extends TestCase {

    public function factoryMethodProvider() {
        return [
            ['forAbstract'],
            ['forConcrete']
        ];
    }

    /**
     * @param string $factoryMethod
     * @return void
     * @dataProvider factoryMethodProvider
     */
    public function testEmptyTypeThrowsException(string $factoryMethod) : void {
        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('Must not pass an empty type to ' . ServiceDefinitionBuilder::class . '::' . $factoryMethod);
        ServiceDefinitionBuilder::$factoryMethod('');
    }

    public function testBuildingTypeForAbstractHasCorrectServiceDefinitionType() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(SimpleServices\FooInterface::class)->build();

        $this->assertSame(SimpleServices\FooInterface::class, $serviceDefinition->getType());
    }

    public function testBuildingTypeForAbstractIsAbstract() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(SimpleServices\FooInterface::class)->build();

        $this->assertTrue($serviceDefinition->isAbstract());
    }

    public function testBuildingTypeForAbstractIsNotConcrete() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(SimpleServices\FooInterface::class)->build();

        $this->assertFalse($serviceDefinition->isConcrete());
    }

    public function testBuildingTypeForAbstractWithNoProfilesSpecifiedIncludesDefault() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(SimpleServices\FooInterface::class)->build();

        $this->assertSame(['default'], $serviceDefinition->getProfiles());
    }

    public function testBuildingTypeForAbstractWithNoImplementedServicesIsEmpty() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(SimpleServices\FooInterface::class)->build();

        $this->assertEmpty($serviceDefinition->getImplementedServices());
    }

    public function testBuildingTypeForConcreteHasCorrectServiceDefinitionType() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooImplementation::class)->build();

        $this->assertSame(SimpleServices\FooImplementation::class, $serviceDefinition->getType());
    }

    public function testBuildingTypeForConcreteIsNotAbstract() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooImplementation::class)->build();

        $this->assertFalse($serviceDefinition->isAbstract());
    }

    public function testBuildingTypeForConcreteIsConcrete() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooImplementation::class)->build();

        $this->assertTrue($serviceDefinition->isConcrete());
    }

    public function testWithImplementedServicesImmutableBuilder() {
        $interfaceServiceDefinition = ServiceDefinitionBuilder::forAbstract(SimpleServices\FooInterface::class)->build();
        $serviceDefinition1 = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooImplementation::class);
        $serviceDefinition2 = $serviceDefinition1->withImplementedService($interfaceServiceDefinition);

        $this->assertNotSame($serviceDefinition1, $serviceDefinition2);
    }

    public function testWithProfilesImmutableBuilder() {
        $serviceDefinition1 = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooImplementation::class);
        $serviceDefinition2 = $serviceDefinition1->withProfiles('dev');

        $this->assertNotSame($serviceDefinition1, $serviceDefinition2);
    }

    public function testWithImplementedServicesContainsServiceDefinition() {
        $interfaceServiceDefinition = ServiceDefinitionBuilder::forAbstract(SimpleServices\FooInterface::class)->build();
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooImplementation::class)->withImplementedService($interfaceServiceDefinition)->build();

        $this->assertContains($interfaceServiceDefinition, $serviceDefinition->getImplementedServices());
    }

    public function testWithMultipleImplementedServicesContainsAllServiceDefinitions() {
        $fooServiceDefinition = ServiceDefinitionBuilder::forAbstract(MultipleImplementedServices\FooInterface::class)->build();
        $barServiceDefinition = ServiceDefinitionBuilder::forAbstract(MultipleImplementedServices\BarInterface::class)->build();
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(MultipleImplementedServices\FooBarImplementation::class)
            ->withImplementedService($barServiceDefinition)
            ->withImplementedService($fooServiceDefinition)
            ->build();

        $this->assertContains($fooServiceDefinition, $serviceDefinition->getImplementedServices());
        $this->assertContains($barServiceDefinition, $serviceDefinition->getImplementedServices());
    }

    public function testWithProfileReplacesDefault() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooInterface::class)->withProfiles('dev')->build();

        $this->assertSame(['dev'], $serviceDefinition->getProfiles());
    }

    public function testWithMultipleProfilesAllIncluded() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooInterface::class)->withProfiles('default', 'dev', 'local')->build();

        $this->assertSame(['default', 'dev', 'local'], $serviceDefinition->getProfiles());
    }

    /**
     * This test is here because in the context of building a Container an abstract means that we CANNOT instantiate
     * the type. We use the implemented services to determine what concrete types are suitable for aliasing to an
     * abstract type. Aliasing an abstract type to an abstract type isn't logical and would result in a Container unable
     * to make certain types.
     *
     * @return void
     */
    public function testAddImplementedServiceToAbstractThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(SimpleServices\FooInterface::class)->build();
        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('Attempted to add an implemented service to abstract type ' . MultipleImplementedServices\FooInterface::class . ' which is not allowed.');
        ServiceDefinitionBuilder::forAbstract(MultipleImplementedServices\FooInterface::class)->withImplementedService($serviceDefinition);
    }

    public function testAddConcreteServiceToConcreteThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooImplementation::class)->build();
        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('Attempted to add a concrete implemented service to a concrete type ' . MultipleImplementedServices\FooBarImplementation::class . ' which is not allowed.');
        ServiceDefinitionBuilder::forConcrete(MultipleImplementedServices\FooBarImplementation::class)->withImplementedService($serviceDefinition);
    }

}