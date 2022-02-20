<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\InterfaceServicePrepare;
use PHPUnit\Framework\TestCase;

class ServicePrepareDefinitionBuilderTest extends TestCase {

    public function testEmptyMethodThrowsException() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(InterfaceServicePrepare\FooInterface::class)->build();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A method for a ServicePrepareDefinition must not be blank.');
        ServicePrepareDefinitionBuilder::forMethod($serviceDefinition, '');
    }

    public function testBuildHasService() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(InterfaceServicePrepare\FooInterface::class)->build();
        $prepareDefinition = ServicePrepareDefinitionBuilder::forMethod($serviceDefinition, 'setBar')->build();

        $this->assertSame($serviceDefinition, $prepareDefinition->getService());
    }

    public function testBuildHasMethod() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(InterfaceServicePrepare\FooInterface::class)->build();
        $prepareDefinition = ServicePrepareDefinitionBuilder::forMethod($serviceDefinition, 'setBar')->build();

        $this->assertSame('setBar', $prepareDefinition->getMethod());
    }

}