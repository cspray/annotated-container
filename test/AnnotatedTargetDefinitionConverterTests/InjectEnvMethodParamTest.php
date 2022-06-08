<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedTarget\AnnotatedTarget;
use Cspray\AnnotatedContainer\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use function Cspray\Typiphy\stringType;

class InjectEnvMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(AttributeType::Inject, new \ReflectionParameter(
             [Fixtures::injectConstructorServices()->injectEnvService()->getName(), '__construct'], 'user'
        ));
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(Fixtures::injectConstructorServices()->injectEnvService(), $this->definition->getTargetIdentifier()->getClass());
    }

    public function testDefinitionGetMethod() {
        $this->assertSame('__construct', $this->definition->getTargetIdentifier()->getMethodName());
    }

    public function testDefinitionGetParamName() {
        $this->assertSame('user', $this->definition->getTargetIdentifier()->getName());
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