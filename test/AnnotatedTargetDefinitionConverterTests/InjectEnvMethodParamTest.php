<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\DummyApps;
use function Cspray\Typiphy\objectType;
use function Cspray\Typiphy\stringType;

class InjectEnvMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(AttributeType::Inject, new \ReflectionParameter(
             [DummyApps\InjectEnvMethodParam\FooImplementation::class, '__construct'], 'stringParam'
        ));
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(objectType(DummyApps\InjectEnvMethodParam\FooImplementation::class), $this->definition->getTargetIdentifier()->getClass());
    }

    public function testDefinitionGetMethod() {
        $this->assertSame('__construct', $this->definition->getTargetIdentifier()->getMethodName());
    }

    public function testDefinitionGetParamName() {
        $this->assertSame('stringParam', $this->definition->getTargetIdentifier()->getName());
    }

    public function testDefinitionGetType() {
        $this->assertSame(stringType(), $this->definition->getType());
    }

    public function testGetValue() {
        $this->assertSame('USER', $this->definition->getValue());
    }

    public function testGetStore() {
        $this->assertSame('env', $this->definition->getStoreName());
    }

    public function testGetProfiles() {
        $this->assertEmpty($this->definition->getProfiles());
    }
}