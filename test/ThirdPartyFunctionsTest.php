<?php

namespace Cspray\AnnotatedContainer;

use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\objectType;
use function Cspray\Typiphy\stringType;

class ThirdPartyFunctionsTest extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    private function getContext() : ContainerDefinitionBuilderContext {
        $builder = ContainerDefinitionBuilder::newDefinition();
        return new class($builder) implements ContainerDefinitionBuilderContext {

            public function __construct(private ContainerDefinitionBuilder $builder) {}

            public function getBuilder(): ContainerDefinitionBuilder {
                return $this->builder;
            }

            public function setBuilder(ContainerDefinitionBuilder $containerDefinitionBuilder) {
                $this->builder = $containerDefinitionBuilder;
            }
        };
    }

    public function testHasServiceDefinitionForType() : void {
        $context = $this->getContext();
        service($context, objectType(DummyApps\SimpleServices\FooInterface::class));

        $containerDefinition = $context->getBuilder()->build();

        $this->assertServiceDefinitionsHaveTypes([
            DummyApps\SimpleServices\FooInterface::class
        ], $containerDefinition->getServiceDefinitions());
    }

    public function testServiceDefinitionReturnsIsInContainerDefinition() {
        $context = $this->getContext();
        $def = service($context, objectType(DummyApps\SimpleServices\FooInterface::class));

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\SimpleServices\FooInterface::class);

        $this->assertSame($serviceDefinition, $def);
    }

    public function testAbstractDefinedServiceIsAbstract() {
        $context = $this->getContext();
        service($context, objectType(DummyApps\SimpleServices\FooInterface::class));

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\SimpleServices\FooInterface::class);

        $this->assertTrue($serviceDefinition?->isAbstract());
    }

    public function testAbstractDefinedServiceGetName() {
        $context = $this->getContext();
        service($context, objectType(DummyApps\SimpleServices\FooInterface::class), 'fooService');

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\SimpleServices\FooInterface::class);

        $this->assertSame('fooService', $serviceDefinition?->getName());
    }

    public function testAbstractDefinedServiceGetProfiles() {
        $context = $this->getContext();
        service($context, objectType(DummyApps\SimpleServices\FooImplementation::class), profiles: ['default', 'dev']);

        $containerDefinition = $context->getBuilder();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\SimpleServices\FooImplementation::class);

        $this->assertSame(['default', 'dev'], $serviceDefinition->getProfiles());
    }

    public function testConcreteServiceIsNotDefined() {
        $context = $this->getContext();
        service($context, objectType(DummyApps\SimpleServices\FooImplementation::class));

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\SimpleServices\FooImplementation::class);

        $this->assertTrue($serviceDefinition?->isConcrete());
    }

    public function testServiceIsPrimary() {
        $context = $this->getContext();
        service($context, objectType(DummyApps\SimpleServices\FooImplementation::class), isPrimary: true);

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\SimpleServices\FooImplementation::class);

        $this->assertTrue($serviceDefinition->isPrimary());
    }

    public function testAddAliasDefinition() {
        $context = $this->getContext();
        $abstract = objectType(DummyApps\SimpleServices\FooInterface::class);
        $concrete = objectType(DummyApps\SimpleServices\FooImplementation::class);
        alias($context, $abstract, $concrete);

        $containerDefinition = $context->getBuilder()->build();
        $this->assertAliasDefinitionsMap([
            [DummyApps\SimpleServices\FooInterface::class, DummyApps\SimpleServices\FooImplementation::class]
        ], $containerDefinition->getAliasDefinitions());
    }

    public function testServiceDelegateDefinition() {
        $context = $this->getContext();
        $service = objectType(DummyApps\ServiceDelegate\ServiceInterface::class);
        serviceDelegate($context, $service, objectType(DummyApps\ServiceDelegate\ServiceFactory::class), 'createService');

        $containerDefinition = $context->getBuilder()->build();

        $this->assertCount(1, $containerDefinition->getServiceDelegateDefinitions());
        $this->assertSame(DummyApps\ServiceDelegate\ServiceInterface::class, $containerDefinition->getServiceDelegateDefinitions()[0]->getServiceType()->getName());
        $this->assertSame(DummyApps\ServiceDelegate\ServiceFactory::class, $containerDefinition->getServiceDelegateDefinitions()[0]->getDelegateType()->getName());
        $this->assertSame('createService', $containerDefinition->getServiceDelegateDefinitions()[0]->getDelegateMethod());
    }

    public function testServicePrepareDefinition() {
        $context = $this->getContext();
        servicePrepare($context, objectType(DummyApps\InterfaceServicePrepare\FooInterface::class), 'setBar');

        $containerDefinition = $context->getBuilder()->build();

        $this->assertServicePrepareTypes([
            [DummyApps\InterfaceServicePrepare\FooInterface::class, 'setBar']
        ], $containerDefinition->getServicePrepareDefinitions());
    }

    public function testNonSharedService() {
        $context = $this->getContext();
        service($context, objectType(DummyApps\NonSharedService\FooImplementation::class), isShared: false);

        $containerDefinition = $context->getBuilder()->build();

        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), DummyApps\NonSharedService\FooImplementation::class);
        $this->assertFalse($serviceDefinition->isShared());
    }

}