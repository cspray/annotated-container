<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Exception\ContainerDefinitionMergeException;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\objectType;
use function Cspray\Typiphy\stringType;

class ContainerDefinitionMergeTest extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    public function testMergeIsImmutable() {
        $container1 = ContainerDefinitionBuilder::newDefinition()->build();
        $container2 = ContainerDefinitionBuilder::newDefinition()->build();

        $container3 = $container1->merge($container2);

        $this->assertNotSame($container1, $container3);
        $this->assertNotSame($container2, $container3);
    }

    public function testMergeHasCorrectServiceDefinitions() {
        $serviceDefinition1 = ServiceDefinitionBuilder::forAbstract(Fixtures::implicitAliasedServices()->fooInterface())->build();
        $serviceDefinition2 = ServiceDefinitionBuilder::forConcrete(Fixtures::implicitAliasedServices()->fooImplementation())->build();

        $container1 = ContainerDefinitionBuilder::newDefinition()->withServiceDefinition($serviceDefinition1)->build();
        $container2 = ContainerDefinitionBuilder::newDefinition()->withServiceDefinition($serviceDefinition2)->build();

        $subject = $container1->merge($container2);

        $this->assertServiceDefinitionsHaveTypes([
            Fixtures::implicitAliasedServices()->fooInterface()->getName(),
            Fixtures::implicitAliasedServices()->fooImplementation()->getName()
        ], $subject->getServiceDefinitions());
    }

    public function testMergeDuplicateServiceDefinitionThrowsException() {
        $class = Fixtures::singleConcreteService()->fooImplementation();
        $serviceDefinition1 = ServiceDefinitionBuilder::forAbstract($class)->build();
        $serviceDefinition2 = ServiceDefinitionBuilder::forAbstract($class)->build();

        $container1 = ContainerDefinitionBuilder::newDefinition()->withServiceDefinition($serviceDefinition1)->build();
        $container2 = ContainerDefinitionBuilder::newDefinition()->withServiceDefinition($serviceDefinition2)->build();

        $this->expectException(ContainerDefinitionMergeException::class);
        $this->expectExceptionMessage('The ContainerDefinition already has a ServiceDefinition for ' . $class);
        $container1->merge($container2);
    }

    public function testMergeHasCorrectAliasDefinitions() {
        $fooServiceDefinition = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())->build();
        $bazServiceDefinition = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->bazImplementation())->build();
        $fooBazAliasDefinition = AliasDefinitionBuilder::forAbstract($fooServiceDefinition->getType())->withConcrete($bazServiceDefinition->getType())->build();

        $container1 = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($fooServiceDefinition)
            ->withServiceDefinition($bazServiceDefinition)
            ->withAliasDefinition($fooBazAliasDefinition)
            ->build();

        $barServiceDefinition = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->barImplementation())->build();
        $fooBarAliasDefinition = AliasDefinitionBuilder::forAbstract($fooServiceDefinition->getType())->withConcrete($barServiceDefinition->getType())->build();

        $container2 = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($barServiceDefinition)
            ->withAliasDefinition($fooBarAliasDefinition)
            ->build();

        $subject = $container1->merge($container2);

        $this->assertAliasDefinitionsMap([
            [Fixtures::ambiguousAliasedServices()->fooInterface(), Fixtures::ambiguousAliasedServices()->bazImplementation()],
            [Fixtures::ambiguousAliasedServices()->fooInterface(), Fixtures::ambiguousAliasedServices()->barImplementation()]
        ], $subject->getAliasDefinitions());
    }

    public function testMergeDuplicateAliasDefinitionThrowsException() {
        $fooServiceDefinition = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())->build();
        $bazServiceDefinition = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->bazImplementation())->build();
        $fooBazAliasDefinition = AliasDefinitionBuilder::forAbstract($fooServiceDefinition->getType())->withConcrete($bazServiceDefinition->getType())->build();

        $container1 = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($fooServiceDefinition)
            ->withServiceDefinition($bazServiceDefinition)
            ->withAliasDefinition($fooBazAliasDefinition)
            ->build();

        $container2 = ContainerDefinitionBuilder::newDefinition()
            ->withAliasDefinition($fooBazAliasDefinition)
            ->build();

        $this->expectException(ContainerDefinitionMergeException::class);
        $this->expectExceptionMessage('The ContainerDefinition already has an AliasDefinition for ' . Fixtures::ambiguousAliasedServices()->fooInterface() . ' aliased to ' . Fixtures::ambiguousAliasedServices()->bazImplementation() . '.');
        $container1->merge($container2);
    }

    public function testMergeHasCorrectServicePrepareDefinitions() {
        $interfaceServiceDefinition = ServiceDefinitionBuilder::forAbstract(Fixtures::classOverridesPrepareServices()->fooInterface())->build();
        $interfaceServicePrepareDefinition = ServicePrepareDefinitionBuilder::forMethod($interfaceServiceDefinition->getType(), 'setBar')->build();

        $container1 = ContainerDefinitionBuilder::newDefinition()->withServicePrepareDefinition($interfaceServicePrepareDefinition)->build();

        $classServiceDefinition = ServiceDefinitionBuilder::forConcrete(Fixtures::classOverridesPrepareServices()->fooImplementation())->build();
        $classServicePrepareDefinition = ServicePrepareDefinitionBuilder::forMethod($classServiceDefinition->getType(), 'setBar')->build();

        $container2 = ContainerDefinitionBuilder::newDefinition()->withServicePrepareDefinition($classServicePrepareDefinition)->build();

        $subject = $container1->merge($container2);

        $this->assertServicePrepareTypes([
            [Fixtures::classOverridesPrepareServices()->fooInterface(), 'setBar'],
            [Fixtures::classOverridesPrepareServices()->fooImplementation(), 'setBar']
        ], $subject->getServicePrepareDefinitions());
    }

    public function testMergeHasCorrectServiceDelegateDefinitions() {
        $container1 = ContainerDefinitionBuilder::newDefinition()->build();

        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(Fixtures::delegatedService()->serviceInterface())->build();
        $serviceDelegateDefinition = ServiceDelegateDefinitionBuilder::forService($serviceDefinition->getType())
            ->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), 'createService')
            ->build();
        $container2 = ContainerDefinitionBuilder::newDefinition()->withServiceDelegateDefinition($serviceDelegateDefinition)->build();

        $subject = $container1->merge($container2);

        $this->assertCount(1, $subject->getServiceDelegateDefinitions());
        $this->assertSame(Fixtures::delegatedService()->serviceInterface()->getName(), $subject->getServiceDelegateDefinitions()[0]->getServiceType()->getName());
        $this->assertSame(Fixtures::delegatedService()->serviceFactory()->getName(), $subject->getServiceDelegateDefinitions()[0]->getDelegateType()->getName());
        $this->assertSame('createService', $subject->getServiceDelegateDefinitions()[0]->getDelegateMethod());
    }

}