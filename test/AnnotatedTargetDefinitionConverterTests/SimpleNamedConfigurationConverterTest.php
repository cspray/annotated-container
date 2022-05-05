<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\DummyApps;

class SimpleNamedConfigurationConverterTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget() : AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Configuration,
            new \ReflectionClass(DummyApps\SimpleNamedConfiguration\MyConfig::class)
        );
    }

    public function testInstanceOf() {
        $this->assertInstanceOf(ConfigurationDefinition::class, $this->definition);
    }

    public function testConfigurationName() {
        $this->assertSame('my-config', $this->definition->getName());
    }
}