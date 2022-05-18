<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\ContainerDefinitionAssertionsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;

class SingleConcreteServiceTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {


    protected function getFixtures() : Fixture {
        return Fixtures::singleConcreteService();
    }

    protected function getExpectedServiceDefinitionCount() : int {
        return 1;
    }

    protected function getExpectedAliasDefinitionCount() : int {
        return 0;
    }

    protected function getExpectedServiceDelegateDefinitionCount() : int {
        return 0;
    }

    protected function getExpectedServicePrepareDefinitionCount() : int {
        return 0;
    }

    protected function getExpectedInjectDefinitionCount() : int {
        return 0;
    }

    protected function getExpectedConfigurationDefinitionCount() : int {
        return 0;
    }

    public function testServiceDefinitionType() : void {
        $serviceDefinition = $this->getServiceDefinition($this->subject->getServiceDefinitions(), Fixtures::singleConcreteService()->fooImplementation()->getName());

        $this->assertNotNull($serviceDefinition);
    }

    public function testServiceDefinitionHasNullName() : void {
        $serviceDefinition = $this->getServiceDefinition($this->subject->getServiceDefinitions(), Fixtures::singleConcreteService()->fooImplementation()->getName());

        $this->assertNull($serviceDefinition?->getName());
    }

    public function testServiceDefinitionIsNotPrimary() : void {
        $serviceDefinition = $this->getServiceDefinition($this->subject->getServiceDefinitions(), Fixtures::singleConcreteService()->fooImplementation()->getName());

        $this->assertFalse($serviceDefinition?->isPrimary());
    }

    public function testServiceDefinitionIsConcrete() : void {
        $serviceDefinition = $this->getServiceDefinition($this->subject->getServiceDefinitions(), Fixtures::singleConcreteService()->fooImplementation()->getName());

        $this->assertTrue($serviceDefinition?->isConcrete());
    }

    public function testServiceDefinitionIsAbstract() : void {
        $serviceDefinition = $this->getServiceDefinition($this->subject->getServiceDefinitions(), Fixtures::singleConcreteService()->fooImplementation()->getName());

        $this->assertFalse($serviceDefinition?->isAbstract());
    }

    public function testServiceDefinitionIsShared() : void {
        $serviceDefinition = $this->getServiceDefinition($this->subject->getServiceDefinitions(), Fixtures::singleConcreteService()->fooImplementation()->getName());

        $this->assertTrue($serviceDefinition?->isShared());
    }

    public function testServiceDefinitionHasEmptyProfiles() : void {
        $serviceDefinition = $this->getServiceDefinition($this->subject->getServiceDefinitions(), Fixtures::singleConcreteService()->fooImplementation()->getName());

        $this->assertEmpty($serviceDefinition->getProfiles());
    }

}