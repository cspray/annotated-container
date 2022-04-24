<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Cspray\AnnotatedContainer\DummyApps\SimpleServices;
use Cspray\AnnotatedContainer\DummyApps\MultipleImplementedServices;
use function Cspray\Typiphy\objectType;

class ServiceDefinitionBuilderTest extends TestCase {

    public function factoryMethodProvider() {
        return [
            ['forAbstract'],
            ['forConcrete']
        ];
    }

    public function testBuildingTypeForAbstractHasCorrectServiceDefinitionType() {
        $type = objectType(SimpleServices\FooInterface::class);
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract($type)->build();

        $this->assertSame($type, $serviceDefinition->getType());
    }

    public function testBuildingTypeForAbstractIsAbstract() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(objectType(SimpleServices\FooInterface::class))
            ->build();

        $this->assertTrue($serviceDefinition->isAbstract());
    }

    public function testBuildingTypeForAbstractIsNotConcrete() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(objectType(SimpleServices\FooInterface::class))
            ->build();

        $this->assertFalse($serviceDefinition->isConcrete());
    }

    public function testBuildingTypeForAbstractWithNoProfilesSpecifiedIncludesDefault() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(objectType(SimpleServices\FooInterface::class))
            ->build();

        $this->assertEmpty($serviceDefinition->getProfiles());
    }

    public function testBuildingTypeForConcreteHasCorrectServiceDefinitionType() {
        $type = objectType(SimpleServices\FooImplementation::class);
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete($type)
            ->build();

        $this->assertSame($type, $serviceDefinition->getType());
    }

    public function testBuildingTypeForConcreteIsNotAbstract() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(objectType(SimpleServices\FooImplementation::class))
            ->build();

        $this->assertFalse($serviceDefinition->isAbstract());
    }

    public function testBuildingTypeForConcreteIsConcrete() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(objectType(SimpleServices\FooImplementation::class))
            ->build();

        $this->assertTrue($serviceDefinition->isConcrete());
    }

    public function testWithProfilesImmutableBuilder() {
        $serviceDefinition1 = ServiceDefinitionBuilder::forConcrete(objectType(SimpleServices\FooImplementation::class));
        $serviceDefinition2 = $serviceDefinition1->withProfiles(['dev']);

        $this->assertNotSame($serviceDefinition1, $serviceDefinition2);
    }

    public function testWithProfileReplacesDefault() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(objectType(SimpleServices\FooInterface::class))
            ->withProfiles(['dev'])->build();

        $this->assertSame(['dev'], $serviceDefinition->getProfiles());
    }

    public function testWithMultipleProfilesAllIncluded() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(objectType(SimpleServices\FooInterface::class))
            ->withProfiles(['default', 'dev', 'local'])
            ->build();

        $this->assertSame(['default', 'dev', 'local'], $serviceDefinition->getProfiles());
    }

    public function testWithSharedReturnsDifferentObject() {
        $a = ServiceDefinitionBuilder::forConcrete(objectType(SimpleServices\FooImplementation::class));
        $b = $a->withShared();

        $this->assertNotSame($a, $b);
    }

    public function testWithNoSharedReturnsDifferentObject() {
        $a = ServiceDefinitionBuilder::forConcrete(objectType(SimpleServices\FooImplementation::class));
        $b = $a->withNotShared();

        $this->assertNotSame($a, $b);
    }

    public function testIsSharedByDefault() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(objectType(SimpleServices\FooImplementation::class))
            ->build();

        $this->assertTrue($serviceDefinition->isShared());
    }

    public function testWithNotShared() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(objectType(SimpleServices\FooImplementation::class))
            ->withNotShared()
            ->build();

        $this->assertFalse($serviceDefinition->isShared());
    }

    public function testWithShared() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(objectType(SimpleServices\FooImplementation::class))
            ->withNotShared()
            ->withShared()
            ->build();

        $this->assertTrue($serviceDefinition->isShared());
    }

}