<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Exception\ContainerDefinitionMergeException;
use PHPUnit\Framework\TestCase;

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
        $serviceDefinition1 = ServiceDefinitionBuilder::forAbstract(DummyApps\SimpleServices\FooInterface::class)->build();
        $serviceDefinition2 = ServiceDefinitionBuilder::forConcrete(DummyApps\SimpleServices\FooImplementation::class)->build();

        $container1 = ContainerDefinitionBuilder::newDefinition()->withServiceDefinition($serviceDefinition1)->build();
        $container2 = ContainerDefinitionBuilder::newDefinition()->withServiceDefinition($serviceDefinition2)->build();

        $subject = $container1->merge($container2);

        $this->assertServiceDefinitionsHaveTypes([
            DummyApps\SimpleServices\FooInterface::class,
            DummyApps\SimpleServices\FooImplementation::class
        ], $subject->getServiceDefinitions());
    }

    public function testMergeDuplicateServiceDefinitionThrowsException() {
        $serviceDefinition1 = ServiceDefinitionBuilder::forAbstract(DummyApps\SimpleServices\FooInterface::class)->build();
        $serviceDefinition2 = ServiceDefinitionBuilder::forAbstract(DummyApps\SimpleServices\FooInterface::class)->build();

        $container1 = ContainerDefinitionBuilder::newDefinition()->withServiceDefinition($serviceDefinition1)->build();
        $container2 = ContainerDefinitionBuilder::newDefinition()->withServiceDefinition($serviceDefinition2)->build();

        $this->expectException(ContainerDefinitionMergeException::class);
        $this->expectExceptionMessage('The ContainerDefinition already has a ServiceDefinition for ' . DummyApps\SimpleServices\FooInterface::class);
        $container1->merge($container2);
    }

    public function testMergeHasCorrectAliasDefinitions() {
        $fooServiceDefinition = ServiceDefinitionBuilder::forAbstract(DummyApps\MultipleAliasResolution\FooInterface::class)->build();
        $bazServiceDefinition = ServiceDefinitionBuilder::forConcrete(DummyApps\MultipleAliasResolution\BazImplementation::class)->build();
        $fooBazAliasDefinition = AliasDefinitionBuilder::forAbstract($fooServiceDefinition)->withConcrete($bazServiceDefinition)->build();

        $container1 = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($fooServiceDefinition)
            ->withServiceDefinition($bazServiceDefinition)
            ->withAliasDefinition($fooBazAliasDefinition)
            ->build();

        $barServiceDefinition = ServiceDefinitionBuilder::forConcrete(DummyApps\MultipleAliasResolution\BarImplementation::class)->build();
        $fooBarAliasDefinition = AliasDefinitionBuilder::forAbstract($fooServiceDefinition)->withConcrete($barServiceDefinition)->build();

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
        $fooServiceDefinition = ServiceDefinitionBuilder::forAbstract(DummyApps\MultipleAliasResolution\FooInterface::class)->build();
        $bazServiceDefinition = ServiceDefinitionBuilder::forConcrete(DummyApps\MultipleAliasResolution\BazImplementation::class)->build();
        $fooBazAliasDefinition = AliasDefinitionBuilder::forAbstract($fooServiceDefinition)->withConcrete($bazServiceDefinition)->build();

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
        $interfaceServiceDefinition = ServiceDefinitionBuilder::forAbstract(DummyApps\InterfaceServicePrepare\FooInterface::class)->build();
        $interfaceServicePrepareDefinition = ServicePrepareDefinitionBuilder::forMethod($interfaceServiceDefinition, 'setBar')->build();

        $container1 = ContainerDefinitionBuilder::newDefinition()->withServicePrepareDefinition($interfaceServicePrepareDefinition)->build();

        $classServiceDefinition = ServiceDefinitionBuilder::forConcrete(DummyApps\ClassOverridesInterfaceServicePrepare\FooImplementation::class)->build();
        $classServicePrepareDefinition = ServicePrepareDefinitionBuilder::forMethod($classServiceDefinition, 'setBar')->build();

        $container2 = ContainerDefinitionBuilder::newDefinition()->withServicePrepareDefinition($classServicePrepareDefinition)->build();

        $subject = $container1->merge($container2);

        $this->assertServicePrepareTypes([
            [DummyApps\InterfaceServicePrepare\FooInterface::class, 'setBar'],
            [DummyApps\ClassOverridesInterfaceServicePrepare\FooImplementation::class, 'setBar']
        ], $subject->getServicePrepareDefinitions());
    }

    public function testMergeHasCorrectInjectScalarDefinitions() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(DummyApps\SimpleUseScalar\FooImplementation::class)->build();
        $injectScalarDefinition1 = InjectScalarDefinitionBuilder::forMethod($serviceDefinition, '__construct')
            ->withParam(ScalarType::String, 'stringParam')
            ->withValue(new CompileEqualsRuntimeAnnotationValue('string value'))
            ->withProfiles(new ArrayAnnotationValue(new CompileEqualsRuntimeAnnotationValue('default')))
            ->build();

        $container1 = ContainerDefinitionBuilder::newDefinition()->withInjectScalarDefinition($injectScalarDefinition1)->build();

        $injectScalarDefinition2 = InjectScalarDefinitionBuilder::forMethod($serviceDefinition, '__construct')
            ->withParam(ScalarType::Int, 'intParam')
            ->withValue(new CompileEqualsRuntimeAnnotationValue(42))
            ->withProfiles(new ArrayAnnotationValue(new CompileEqualsRuntimeAnnotationValue('default')))
            ->build();

        $container2 = ContainerDefinitionBuilder::newDefinition()->withInjectScalarDefinition($injectScalarDefinition2)->build();

        $subject = $container1->merge($container2);

        $this->assertInjectScalarParamValues([
            DummyApps\SimpleUseScalar\FooImplementation::class . '::__construct(stringParam)|default' => 'string value',
            DummyApps\SimpleUseScalar\FooImplementation::class . '::__construct(intParam)|default' => 42
        ], $subject->getInjectScalarDefinitions());
    }

    public function testMergeHasCorrectInjectServiceDefinitions() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(DummyApps\SimpleUseService\ConstructorInjection::class)->build();
        $barInjectedDefinition = new CompileEqualsRuntimeAnnotationValue(DummyApps\SimpleUseService\BarImplementation::class);
        $barInjectServiceDefinition = InjectServiceDefinitionBuilder::forMethod($serviceDefinition, '__construct')
            ->withParam(DummyApps\SimpleUseService\FooInterface::class, 'bar')
            ->withInjectedService($barInjectedDefinition)
            ->build();
        $container1 = ContainerDefinitionBuilder::newDefinition()->withInjectServiceDefinition($barInjectServiceDefinition)->build();

        $bazInjectedDefinition = new CompileEqualsRuntimeAnnotationValue(DummyApps\SimpleUseService\BazImplementation::class);
        $bazInjectServiceDefinition = InjectServiceDefinitionBuilder::forMethod($serviceDefinition, '__construct')
            ->withParam(DummyApps\SimpleUseService\BazImplementation::class, 'baz')
            ->withInjectedService($bazInjectedDefinition)
            ->build();
        $container2 = ContainerDefinitionBuilder::newDefinition()->withInjectServiceDefinition($bazInjectServiceDefinition)->build();

        $subject = $container1->merge($container2);

        $this->assertUseServiceParamValues([
            DummyApps\SimpleUseService\ConstructorInjection::class . '::__construct(bar)' => DummyApps\SimpleUseService\BarImplementation::class,
            DummyApps\SimpleUseService\ConstructorInjection::class . '::__construct(baz)' => DummyApps\SimpleUseService\BazImplementation::class
        ], $subject->getInjectServiceDefinitions());
    }

    public function testMergeHasCorrectServiceDelegateDefinitions() {
        $container1 = ContainerDefinitionBuilder::newDefinition()->build();

        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(DummyApps\ServiceDelegate\ServiceInterface::class)->build();
        $serviceDelegateDefinition = ServiceDelegateDefinitionBuilder::forService($serviceDefinition)
            ->withDelegateMethod(DummyApps\ServiceDelegate\ServiceFactory::class, 'createService')
            ->build();
        $container2 = ContainerDefinitionBuilder::newDefinition()->withServiceDelegateDefinition($serviceDelegateDefinition)->build();

        $subject = $container1->merge($container2);

        $this->assertCount(1, $subject->getServiceDelegateDefinitions());
        $this->assertSame(DummyApps\ServiceDelegate\ServiceInterface::class, $subject->getServiceDelegateDefinitions()[0]->getServiceType()->getType());
        $this->assertSame(DummyApps\ServiceDelegate\ServiceFactory::class, $subject->getServiceDelegateDefinitions()[0]->getDelegateType());
        $this->assertSame('createService', $subject->getServiceDelegateDefinitions()[0]->getDelegateMethod());
    }

}