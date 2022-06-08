<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedTarget\AnnotatedTarget;
use Cspray\AnnotatedContainer\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use function Cspray\Typiphy\floatType;
use function Cspray\Typiphy\typeUnion;

class InjectServiceScalarTypeUnionMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionParameter([Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector()->getName(), 'setValue'], 'val')
        );
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(), $this->definition->getTargetIdentifier()->getClass());
    }

    public function testDefinitionGetMethod() {
        $this->assertSame('setValue', $this->definition->getTargetIdentifier()->getMethodName());
    }

    public function testDefinitionGetParamName() {
        $this->assertSame('val', $this->definition->getTargetIdentifier()->getName());
    }

    public function testDefinitionGetTypeUnion() {
        $this->assertSame(typeUnion(floatType(), Fixtures::injectPrepareServices()->fooInterface()), $this->definition->getType());
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