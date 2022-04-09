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

        $this->assertEmpty($serviceDefinition->getProfiles()->getCompileValue());
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

    public function testWithProfilesImmutableBuilder() {
        $serviceDefinition1 = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooImplementation::class);
        $serviceDefinition2 = $serviceDefinition1->withProfiles(arrayValue(['dev']));

        $this->assertNotSame($serviceDefinition1, $serviceDefinition2);
    }

    public function testWithProfileReplacesDefault() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooInterface::class)->withProfiles(arrayValue(['dev']))->build();

        $this->assertSame(['dev'], $serviceDefinition->getProfiles()->getRuntimeValue());
    }

    public function testWithMultipleProfilesAllIncluded() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(SimpleServices\FooInterface::class)
            ->withProfiles(arrayValue(['default', 'dev', 'local']))
            ->build();

        $this->assertSame(['default', 'dev', 'local'], $serviceDefinition->getProfiles()->getRuntimeValue());
    }

}