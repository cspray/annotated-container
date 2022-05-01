<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\DummyApps;
use function Cspray\Typiphy\boolType;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\objectType;

class InjectBoolMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget() : AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionParameter([DummyApps\InjectBoolMethodParam\FooImplementation::class, '__construct'],
            'flag'
        ));
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(objectType(DummyApps\InjectBoolMethodParam\FooImplementation::class), $this->definition->getTargetIdentifier()->getClass());
    }

    public function testDefinitionGetMethod() {
        $this->assertSame('__construct', $this->definition->getTargetIdentifier()->getMethodName());
    }

    public function testDefinitionGetParamName() {
        $this->assertSame('flag', $this->definition->getTargetIdentifier()->getName());
    }

    public function testDefinitionGetType() {
        $this->assertSame(boolType(), $this->definition->getType());
    }

    public function testGetValue() {
        $this->assertTrue($this->definition->getValue());
    }

    public function testGetStore() {
        $this->assertNull($this->definition->getStoreName());
    }

    public function testGetProfiles() {
        $this->assertEmpty($this->definition->getProfiles());
    }
}