<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\DummyApps;
use function Cspray\Typiphy\floatType;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\objectType;

class InjectFloatMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget() : AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionParameter([DummyApps\InjectFloatMethodParam\FooImplementation::class, 'setSomething'],
            'pie'
        ));
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(objectType(DummyApps\InjectFloatMethodParam\FooImplementation::class), $this->definition->getTargetIdentifier()->getClass());
    }

    public function testDefinitionGetMethod() {
        $this->assertSame('setSomething', $this->definition->getTargetIdentifier()->getMethodName());
    }

    public function testDefinitionGetParamName() {
        $this->assertSame('pie', $this->definition->getTargetIdentifier()->getName());
    }

    public function testDefinitionGetType() {
        $this->assertSame(floatType(), $this->definition->getType());
    }

    public function testGetValue() {
        $this->assertSame(3.14, $this->definition->getValue());
    }

    public function testGetStore() {
        $this->assertNull($this->definition->getStoreName());
    }

    public function testGetProfiles() {
        $this->assertEmpty($this->definition->getProfiles());
    }
}