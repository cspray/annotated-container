<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetParserTests;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainerFixture\Fixtures;

class MultiplePropsConfigurationParserTest extends AnnotatedTargetParserTestCase {

    protected function getDirectories() : array {
        return [Fixtures::configurationServices()->getPath()];
    }

    public function testCountExpectedTargets() {
        $this->assertCount(9, $this->targets);
    }

    public function testConfigurationReflectionTargetGetName() {
        $annotatedTarget = $this->targets[8];

        $this->assertSame(Fixtures::configurationServices()->multiPropConfig()->getName(), $annotatedTarget->getTargetReflection()->getName());
    }

    public function testConfigurationReflectionAttributeGetName() {
        $annotatedTarget = $this->targets[8];

        $this->assertSame(Configuration::class, $annotatedTarget->getAttributeReflection()->getName());
    }

    public function testConfigurationReflectionAttributeInstanceOf() {
        $annotatedTarget = $this->targets[8];

        $this->assertInstanceOf(Configuration::class, $annotatedTarget->getAttributeInstance());
    }

    public function injectNameProvider() : array {
        return [
            [6, 'foo'],
            [7, 'bar']
        ];
    }

    /**
     * @dataProvider injectNameProvider
     */
    public function testInjectReflectionTargetGetName(int $index, string $name) {
        $annotatedTarget = $this->targets[$index];

        $this->assertSame($name, $annotatedTarget->getTargetReflection()->getName());
    }

    public function injectInstanceOfProvider() : array {
        return [
            [6, Inject::class],
            [7, Inject::class]
        ];
    }

    /**
     * @dataProvider injectInstanceOfProvider
     */
    public function testInjectReflectionAttributeGetName(int $index, string $class) {
        $annotatedTarget = $this->targets[$index];

        $this->assertSame($class, $annotatedTarget->getAttributeReflection()->getName());
    }

    public function injectValueProvider() : array {
        return [
            [6, 'baz'],
            [7, 'baz'],
        ];
    }

    /**
     * @dataProvider injectValueProvider
     */
    public function testInjectReflectionAttributeInstanceValue(int $index, string $value) {
        $annotatedTarget = $this->targets[$index];

        $this->assertSame($value, $annotatedTarget->getAttributeInstance()->value);
    }

    public function injectProfilesProvider() : array {
        return [
            [6, []],
            [7, []]
        ];
    }

    /**
     * @dataProvider injectProfilesProvider
     */
    public function testInjectProfilesValues(int $index, array $profiles) {
        $annotatedTarget = $this->targets[$index];
        $this->assertSame($profiles, $annotatedTarget->getAttributeInstance()->profiles);
    }

}