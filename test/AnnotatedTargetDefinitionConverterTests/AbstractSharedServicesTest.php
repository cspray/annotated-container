<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\DummyApps;
use Cspray\AnnotatedContainer\ServiceDefinition;
use ReflectionClass;
use function Cspray\Typiphy\objectType;

class AbstractSharedServicesTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(AttributeType::Service, new ReflectionClass(
            DummyApps\AbstractSharedServices\AbstractFoo::class
        ));
    }

    public function testGetServiceDefinitionInstance() {
        $this->assertInstanceOf(ServiceDefinition::class, $this->definition);
    }

    public function testGetServiceDefinitionType() {
        $this->assertSame(objectType(DummyApps\AbstractSharedServices\AbstractFoo::class), $this->definition->getType());
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