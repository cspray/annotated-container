<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use PHPUnit\Framework\TestCase;
use Cspray\AnnotatedContainer\DummyApps;
use function Cspray\Typiphy\objectType;

class ConfigurationDefinitionBuilderTest extends TestCase {

    public function testWithNameDifferentObject() {
        $configDefinition = ConfigurationDefinitionBuilder::forClass(objectType(DummyApps\SimpleConfiguration\MyConfig::class));

        $a = $configDefinition->withName('foo');
        $b = $a->withName('bar');

        $this->assertNotSame($a, $b);
    }

    public function testForServiceBuild() {
        $configurationDefinition = ConfigurationDefinitionBuilder::forClass($configType = objectType(DummyApps\SimpleConfiguration\MyConfig::class))->build();

        $this->assertSame($configType, $configurationDefinition->getClass());
    }

    public function testWithNameBuild() {
        $configurationDefinition = ConfigurationDefinitionBuilder::forClass(objectType(DummyApps\SimpleConfiguration\MyConfig::class))
            ->withName('my-config')
            ->build();

        $this->assertSame('my-config', $configurationDefinition->getName());
    }

}