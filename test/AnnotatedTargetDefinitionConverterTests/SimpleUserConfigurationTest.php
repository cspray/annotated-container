<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\DummyApps;
use function Cspray\Typiphy\boolType;
use function Cspray\Typiphy\stringType;

class SimpleUserConfigurationTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget() : AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionProperty(DummyApps\SimpleConfiguration\MyConfig::class, 'user'),
            0
        );
    }

    public function testGetDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testGetTargetReflectionClass() {
        $this->assertSame(DummyApps\SimpleConfiguration\MyConfig::class, $this->definition->getTargetIdentifier()->getClass()->getName());
    }

    public function testGetTargetReflectionName() {
        $this->assertSame('user', $this->definition->getTargetIdentifier()->getName());
    }

    public function testGetDefinitionType() {
        $this->assertSame(stringType(), $this->definition->getType());
    }

    public function testGetDefinitionValue() {
        $this->assertSame('USER', $this->definition->getValue());
    }

    public function testGetDefinitionProfiles() {
        $this->assertSame([], $this->definition->getProfiles());
    }

    public function testGetDefinitionStore() {
        $this->assertSame('env', $this->definition->getStoreName());
    }

}