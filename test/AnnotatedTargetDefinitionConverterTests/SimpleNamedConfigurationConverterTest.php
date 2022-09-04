<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;

class SimpleNamedConfigurationConverterTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget() : AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Configuration,
            new \ReflectionClass(Fixtures::namedConfigurationServices()->myConfig()->getName())
        );
    }

    public function testInstanceOf() {
        $this->assertInstanceOf(ConfigurationDefinition::class, $this->definition);
    }

    public function testConfigurationName() {
        $this->assertSame('my-config', $this->definition->getName());
    }

    public function testGetAttribute() : void {
        self::assertInstanceOf(Configuration::class, $this->definition->getAttribute());
    }
}