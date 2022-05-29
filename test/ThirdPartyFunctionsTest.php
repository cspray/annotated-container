<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainerFixture\Fixtures;
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

            public function setBuilder(ContainerDefinitionBuilder $containerDefinitionBuilder) : void {
                $this->builder = $containerDefinitionBuilder;
            }
        };
    }

    public function testHasServiceDefinitionForType() : void {
        $context = $this->getContext();
        $type = Fixtures::singleConcreteService()->fooImplementation();
        service($context, $type);

        $containerDefinition = $context->getBuilder()->build();

        $this->assertServiceDefinitionsHaveTypes([$type->getName()], $containerDefinition->getServiceDefinitions());
    }

    public function testServiceDefinitionReturnsIsInContainerDefinition() {
        $context = $this->getContext();
        $def = service($context, Fixtures::singleConcreteService()->fooImplementation());

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), Fixtures::singleConcreteService()->fooImplementation()->getName());

        $this->assertSame($serviceDefinition, $def);
    }

    public function testAbstractDefinedServiceIsAbstract() {
        $context = $this->getContext();
        service($context, Fixtures::implicitAliasedServices()->fooInterface());

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), Fixtures::implicitAliasedServices()->fooInterface()->getName());

        $this->assertTrue($serviceDefinition?->isAbstract());
    }

    public function testAbstractDefinedServiceGetName() {
        $context = $this->getContext();
        service($context, Fixtures::implicitAliasedServices()->fooInterface(), 'fooService');

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), Fixtures::implicitAliasedServices()->fooInterface()->getName());

        $this->assertSame('fooService', $serviceDefinition?->getName());
    }

    public function testAbstractDefinedServiceGetProfiles() {
        $context = $this->getContext();
        service($context, Fixtures::implicitAliasedServices()->fooInterface(), profiles: ['default', 'dev']);

        $containerDefinition = $context->getBuilder();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), Fixtures::implicitAliasedServices()->fooInterface()->getName());

        $this->assertSame(['default', 'dev'], $serviceDefinition->getProfiles());
    }

    public function testSingleConcreteServiceIsConcrete() {
        $context = $this->getContext();
        service($context, Fixtures::singleConcreteService()->fooImplementation());

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), Fixtures::singleConcreteService()->fooImplementation()->getName());

        $this->assertTrue($serviceDefinition?->isConcrete());
    }

    public function testSingleConcreteServiceIsPrimary() {
        $context = $this->getContext();
        service($context, Fixtures::singleConcreteService()->fooImplementation(), isPrimary: true);

        $containerDefinition = $context->getBuilder()->build();
        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), Fixtures::singleConcreteService()->fooImplementation()->getName());

        $this->assertTrue($serviceDefinition->isPrimary());
    }

    public function testAddAliasDefinition() {
        $context = $this->getContext();
        $abstract = Fixtures::implicitAliasedServices()->fooInterface();
        $concrete = Fixtures::implicitAliasedServices()->fooImplementation();
        alias($context, $abstract, $concrete);

        $containerDefinition = $context->getBuilder()->build();
        $this->assertAliasDefinitionsMap([
            [Fixtures::implicitAliasedServices()->fooInterface()->getName(), Fixtures::implicitAliasedServices()->fooImplementation()->getName()]
        ], $containerDefinition->getAliasDefinitions());
    }

    public function testServiceDelegateDefinition() {
        $context = $this->getContext();
        $service = Fixtures::delegatedService()->serviceInterface();
        serviceDelegate($context, $service, Fixtures::delegatedService()->serviceFactory(), 'createService');

        $containerDefinition = $context->getBuilder()->build();

        $this->assertCount(1, $containerDefinition->getServiceDelegateDefinitions());
        $this->assertSame(Fixtures::delegatedService()->serviceInterface()->getName(), $containerDefinition->getServiceDelegateDefinitions()[0]->getServiceType()->getName());
        $this->assertSame(Fixtures::delegatedService()->serviceFactory()->getName(), $containerDefinition->getServiceDelegateDefinitions()[0]->getDelegateType()->getName());
        $this->assertSame('createService', $containerDefinition->getServiceDelegateDefinitions()[0]->getDelegateMethod());
    }

    public function testServicePrepareDefinition() {
        $context = $this->getContext();
        servicePrepare($context, Fixtures::interfacePrepareServices()->fooInterface(), 'setBar');

        $containerDefinition = $context->getBuilder()->build();

        $this->assertServicePrepareTypes([
            [Fixtures::interfacePrepareServices()->fooInterface()->getName(), 'setBar']
        ], $containerDefinition->getServicePrepareDefinitions());
    }

    public function testNonSharedService() {
        $context = $this->getContext();
        service($context, Fixtures::nonSharedServices()->fooImplementation(), isShared: false);

        $containerDefinition = $context->getBuilder()->build();

        $serviceDefinition = $this->getServiceDefinition($containerDefinition->getServiceDefinitions(), Fixtures::nonSharedServices()->fooImplementation()->getName());
        $this->assertFalse($serviceDefinition->isShared());
    }

}