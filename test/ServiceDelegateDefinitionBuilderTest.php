<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\ServiceDelegate;
use PHPUnit\Framework\TestCase;

class ServiceDelegateDefinitionBuilderTest extends TestCase {

    public function testWithEmptyDelegateTypeThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(ServiceDelegate\ServiceInterface::class)->build();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The delegate type for a ServiceDelegateDefinition must not be blank.');
        ServiceDelegateDefinitionBuilder::forService($serviceDefinition)->withDelegateMethod('', '');
    }

    public function testWithEmptyDelegateMethodThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(ServiceDelegate\ServiceInterface::class)->build();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The delegate method for a ServiceDelegateDefinition must not be blank.');
        ServiceDelegateDefinitionBuilder::forService($serviceDefinition)->withDelegateMethod(ServiceDelegate\ServiceFactory::class, '');
    }

    public function testWithDelegateMethodImmutableBuilder() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(ServiceDelegate\ServiceInterface::class)->build();
        $builder1 = ServiceDelegateDefinitionBuilder::forService($serviceDefinition);
        $builder2 = $builder1->withDelegateMethod(ServiceDelegate\ServiceFactory::class, 'createService');

        $this->assertNotSame($builder1, $builder2);
    }

    public function testBuildHasServiceDefinition() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(ServiceDelegate\ServiceInterface::class)->build();
        $delegateDefinition = ServiceDelegateDefinitionBuilder::forService($serviceDefinition)
            ->withDelegateMethod(ServiceDelegate\ServiceFactory::class, 'createService')
            ->build();

        $this->assertSame($serviceDefinition, $delegateDefinition->getServiceType());
    }

    public function testBuildHasDelegateType() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(ServiceDelegate\ServiceInterface::class)->build();
        $delegateDefinition = ServiceDelegateDefinitionBuilder::forService($serviceDefinition)
            ->withDelegateMethod(ServiceDelegate\ServiceFactory::class, 'createService')
            ->build();

        $this->assertSame(ServiceDelegate\ServiceFactory::class, $delegateDefinition->getDelegateType());
    }

    public function testBuildHasDelegateMethod() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(ServiceDelegate\ServiceInterface::class)->build();
        $delegateDefinition = ServiceDelegateDefinitionBuilder::forService($serviceDefinition)
            ->withDelegateMethod(ServiceDelegate\ServiceFactory::class, 'createService')
            ->build();

        $this->assertSame('createService', $delegateDefinition->getDelegateMethod());
    }

}