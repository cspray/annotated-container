<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\DummyApps;
use function Cspray\Typiphy\arrayType;
use function Cspray\Typiphy\boolType;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\mixedType;
use function Cspray\Typiphy\objectType;

class InjectImplicitMixedMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget() : AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionParameter([DummyApps\InjectImplicitMixedMethodParam\FooImplementation::class, '__construct'],
            'item'
        ));
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(objectType(DummyApps\InjectImplicitMixedMethodParam\FooImplementation::class), $this->definition->getTargetIdentifier()->getClass());
    }

    public function testDefinitionGetMethod() {
        $this->assertSame('__construct', $this->definition->getTargetIdentifier()->getMethodName());
    }

    public function testDefinitionGetParamName() {
        $this->assertSame('item', $this->definition->getTargetIdentifier()->getName());
    }

    public function testDefinitionGetType() {
        $this->assertSame(mixedType(), $this->definition->getType());
    }

    public function testGetValue() {
        $this->assertSame('anything', $this->definition->getValue());
    }

    public function testGetStore() {
        $this->assertNull($this->definition->getStoreName());
    }

    public function testGetProfiles() {
        $this->assertEmpty($this->definition->getProfiles());
    }
}