<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests;

use Cspray\AnnotatedContainer\ServiceDefinition;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;

class SingleAliasedServiceTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    protected function getFixtures() : Fixture {
        return Fixtures::singleAliasedService();
    }

    protected function getExpectedServiceDefinitionCount() : int {
        return 2;
    }

    protected function getExpectedAliasDefinitionCount() : int {
        return 1;
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

    private function getAbstractService() : ?ServiceDefinition {
        return $this->getServiceDefinition($this->subject->getServiceDefinitions(), Fixtures::singleAliasedService()->fooInterface()->getName());
    }

    private function getConcreteService() : ?ServiceDefinition {
        return $this->getServiceDefinition($this->subject->getServiceDefinitions(), Fixtures::singleAliasedService()->fooImplementation()->getName());
    }

    public function testAbstractServiceDefinitionType() {
        $this->assertNotNull($this->getAbstractService());
    }

    public function testAbstractServiceDefinitionHasNullName() {
        $this->assertNull($this->getAbstractService()?->getName());
    }

    public function testAbstractServiceDefinitionNotPrimary() {
        $this->assertFalse($this->getAbstractService()?->isPrimary());
    }

    public function testAbstractServiceDefinitionIsConcrete() {
        $this->assertFalse($this->getAbstractService()?->isConcrete());
    }

    public function testAbstractServiceDefinitionIsAbstract() {
        $this->assertTrue($this->getAbstractService()?->isAbstract());
    }

    public function testAbstractServiceDefinitionShared() {
        $this->assertTrue($this->getAbstractService()?->isShared());
    }

    public function testAbstractServiceDefinitionHasEmptyProfiles() {
        $this->assertEmpty($this->getAbstractService()?->getProfiles());
    }

    public function testConcreteServiceDefinitionType() {
        $this->assertNotNull($this->getConcreteService());
    }

    public function testConcreteServiceDefinitionHasNullName() {
        $this->assertNull($this->getConcreteService()?->getName());
    }

    public function testConcreteServiceDefinitionNotPrimary() {
        $this->assertFalse($this->getConcreteService()?->isPrimary());
    }

    public function testConcreteServiceDefinitionIsConcrete() {
        $this->assertTrue($this->getConcreteService()?->isConcrete());
    }

    public function testConcreteServiceDefinitionIsAbstract() {
        $this->assertFalse($this->getConcreteService()?->isAbstract());
    }

    public function testConcreteServiceDefinitionShared() {
        $this->assertTrue($this->getConcreteService()?->isShared());
    }

    public function testConcreteServiceDefinitionEmptyProfiles() {
        $this->assertEmpty($this->getConcreteService()?->getProfiles());
    }

    public function testAliasDefinitionMap() {
        $this->assertAliasDefinitionsMap([
            [$this->getAbstractService()->getType()->getName(), $this->getConcreteService()->getType()->getName()]
        ], $this->subject->getAliasDefinitions());
    }

}