<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Definition\ConfigurationDefinitionBuilder;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;

class ConfigurationDefinitionBuilderTest extends TestCase {

    public function testWithNameDifferentObject() {
        $configDefinition = ConfigurationDefinitionBuilder::forClass(Fixtures::configurationServices()->myConfig());

        $a = $configDefinition->withName('foo');
        $b = $a->withName('bar');

        $this->assertNotSame($a, $b);
    }

    public function testForServiceBuild() {
        $configurationDefinition = ConfigurationDefinitionBuilder::forClass(Fixtures::configurationServices()->myConfig())->build();

        $this->assertSame(Fixtures::configurationServices()->myConfig(), $configurationDefinition->getClass());
    }

    public function testWithNameBuild() {
        $configurationDefinition = ConfigurationDefinitionBuilder::forClass(Fixtures::configurationServices()->myConfig())
            ->withName('my-config')
            ->build();

        $this->assertSame('my-config', $configurationDefinition->getName());
    }

}