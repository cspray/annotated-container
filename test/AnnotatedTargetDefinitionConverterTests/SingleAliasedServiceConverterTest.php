<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\ServiceDefinition;
use Cspray\AnnotatedContainerFixture\Fixtures;
use ReflectionClass;
use function Cspray\Typiphy\objectType;

class SingleAliasedServiceConverterTest extends AnnotatedTargetDefinitionConverterTestCase {

    private function getClass() : string {
        return Fixtures::implicitAliasedServices()->fooInterface()->getName();
    }

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(AttributeType::Service, new ReflectionClass($this->getClass()));
    }

    public function testGetServiceDefinitionInstance() {
        $this->assertInstanceOf(ServiceDefinition::class, $this->definition);
    }

    public function testGetServiceDefinitionType() {
        $this->assertSame(objectType($this->getClass()), $this->definition->getType());
    }

    public function testServiceIsAbstract() {
        $this->assertTrue($this->definition->isAbstract());
    }

    public function testServiceIsShared() {
        $this->assertTrue($this->definition->isShared());
    }

    public function testServiceNameIsNull() {
        $this->assertNull($this->definition->getName());
    }

    public function testServiceIsPrimary() {
        $this->assertFalse($this->definition->isPrimary());
    }

    public function testServiceProfiles() {
        $this->assertSame([], $this->definition->getProfiles());
    }
}