<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Exception\ContainerDefinitionMergeException;
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
        $serviceDefinition1 = ServiceDefinitionBuilder::forAbstract(objectType(DummyApps\SimpleServices\FooInterface::class))->build();
        $serviceDefinition2 = ServiceDefinitionBuilder::forConcrete(objectType(DummyApps\SimpleServices\FooImplementation::class))->build();

        $container1 = ContainerDefinitionBuilder::newDefinition()->withServiceDefinition($serviceDefinition1)->build();
        $container2 = ContainerDefinitionBuilder::newDefinition()->withServiceDefinition($serviceDefinition2)->build();

        $subject = $container1->merge($container2);

        $this->assertServiceDefinitionsHaveTypes([
            DummyApps\SimpleServices\FooInterface::class,
            DummyApps\SimpleServices\FooImplementation::class
        ], $subject->getServiceDefinitions());
    }

    public function testMergeDuplicateServiceDefinitionThrowsException() {
        $serviceDefinition1 = ServiceDefinitionBuilder::forAbstract(objectType(DummyApps\SimpleServices\FooInterface::class))->build();
        $serviceDefinition2 = ServiceDefinitionBuilder::forAbstract(objectType(DummyApps\SimpleServices\FooInterface::class))->build();

        $container1 = ContainerDefinitionBuilder::newDefinition()->withServiceDefinition($serviceDefinition1)->build();
        $container2 = ContainerDefinitionBuilder::newDefinition()->withServiceDefinition($serviceDefinition2)->build();

        $this->expectException(ContainerDefinitionMergeException::class);
        $this->expectExceptionMessage('The ContainerDefinition already has a ServiceDefinition for ' . DummyApps\SimpleServices\FooInterface::class);
        $container1->merge($container2);
    }

    public function testMergeHasCorrectAliasDefinitions() {
        $fooServiceDefinition = ServiceDefinitionBuilder::forAbstract(objectType(DummyApps\MultipleAliasResolution\FooInterface::class))->build();
        $bazServiceDefinition = ServiceDefinitionBuilder::forConcrete(objectType(DummyApps\MultipleAliasResolution\BazImplementation::class))->build();
        $fooBazAliasDefinition = AliasDefinitionBuilder::forAbstract($fooServiceDefinition->getType())->withConcrete($bazServiceDefinition->getType())->build();

        $container1 = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($fooServiceDefinition)
            ->withServiceDefinition($bazServiceDefinition)
            ->withAliasDefinition($fooBazAliasDefinition)
            ->build();

        $barServiceDefinition = ServiceDefinitionBuilder::forConcrete(objectType(DummyApps\MultipleAliasResolution\BarImplementation::class))->build();
        $fooBarAliasDefinition = AliasDefinitionBuilder::forAbstract($fooServiceDefinition->getType())->withConcrete($barServiceDefinition->getType())->build();

        $container2 = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($barServiceDefinition)
            ->withAliasDefinition($fooBarAliasDefinition)
            ->build();

        $subject = $container1->merge($container2);

        $this->assertAliasDefinitionsMap([
            [DummyApps\MultipleAliasResolution\FooInterface::class, DummyApps\MultipleAliasResolution\BazImplementation::class],
            [DummyApps\MultipleAliasResolution\FooInterface::class, DummyApps\MultipleAliasResolution\BarImplementation::class]
        ], $subject->getAliasDefinitions());
    }

    public function testMergeDuplicateAliasDefinitionThrowsException() {
        $fooServiceDefinition = ServiceDefinitionBuilder::forAbstract(objectType(DummyApps\MultipleAliasResolution\FooInterface::class))->build();
        $bazServiceDefinition = ServiceDefinitionBuilder::forConcrete(objectType(DummyApps\MultipleAliasResolution\BazImplementation::class))->build();
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
        $this->expectExceptionMessage('The ContainerDefinition already has an AliasDefinition for ' . DummyApps\MultipleAliasResolution\FooInterface::class . ' aliased to ' . DummyApps\MultipleAliasResolution\BazImplementation::class . '.');
        $container1->merge($container2);
    }

    public function testMergeHasCorrectServicePrepareDefinitions() {
        $interfaceServiceDefinition = ServiceDefinitionBuilder::forAbstract(objectType(DummyApps\InterfaceServicePrepare\FooInterface::class))->build();
        $interfaceServicePrepareDefinition = ServicePrepareDefinitionBuilder::forMethod($interfaceServiceDefinition->getType(), 'setBar')->build();

        $container1 = ContainerDefinitionBuilder::newDefinition()->withServicePrepareDefinition($interfaceServicePrepareDefinition)->build();

        $classServiceDefinition = ServiceDefinitionBuilder::forConcrete(objectType(DummyApps\ClassOverridesInterfaceServicePrepare\FooImplementation::class))->build();
        $classServicePrepareDefinition = ServicePrepareDefinitionBuilder::forMethod($classServiceDefinition->getType(), 'setBar')->build();

        $container2 = ContainerDefinitionBuilder::newDefinition()->withServicePrepareDefinition($classServicePrepareDefinition)->build();

        $subject = $container1->merge($container2);

        $this->assertServicePrepareTypes([
            [DummyApps\InterfaceServicePrepare\FooInterface::class, 'setBar'],
            [DummyApps\ClassOverridesInterfaceServicePrepare\FooImplementation::class, 'setBar']
        ], $subject->getServicePrepareDefinitions());
    }

    public function testMergeHasCorrectServiceDelegateDefinitions() {
        $container1 = ContainerDefinitionBuilder::newDefinition()->build();

        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(objectType(DummyApps\ServiceDelegate\ServiceInterface::class))->build();
        $serviceDelegateDefinition = ServiceDelegateDefinitionBuilder::forService($serviceDefinition->getType())
            ->withDelegateMethod(objectType(DummyApps\ServiceDelegate\ServiceFactory::class), 'createService')
            ->build();
        $container2 = ContainerDefinitionBuilder::newDefinition()->withServiceDelegateDefinition($serviceDelegateDefinition)->build();

        $subject = $container1->merge($container2);

        $this->assertCount(1, $subject->getServiceDelegateDefinitions());
        $this->assertSame(DummyApps\ServiceDelegate\ServiceInterface::class, $subject->getServiceDelegateDefinitions()[0]->getServiceType()->getName());
        $this->assertSame(DummyApps\ServiceDelegate\ServiceFactory::class, $subject->getServiceDelegateDefinitions()[0]->getDelegateType()->getName());
        $this->assertSame('createService', $subject->getServiceDelegateDefinitions()[0]->getDelegateMethod());
    }

}