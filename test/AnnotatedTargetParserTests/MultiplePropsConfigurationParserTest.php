<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetParserTests;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainerFixture\Fixtures;

class MultiplePropsConfigurationParserTest extends AnnotatedTargetParserTestCase {

    protected function getDirectories() : array {
        return [Fixtures::multiPropConfigurationServices()->getPath()];
    }

    public function testCountExpectedTargets() {
        $this->assertCount(3, $this->targets);
    }

    public function testConfigurationReflectionTargetGetName() {
        $annotatedTarget = $this->targets[2];

        $this->assertSame(Fixtures::multiPropConfigurationServices()->myConfig()->getName(), $annotatedTarget->getTargetReflection()->getName());
    }

    public function testConfigurationReflectionAttributeGetName() {
        $annotatedTarget = $this->targets[2];

        $this->assertSame(Configuration::class, $annotatedTarget->getAttributeReflection()->getName());
    }

    public function testConfigurationReflectionAttributeInstanceOf() {
        $annotatedTarget = $this->targets[2];

        $this->assertInstanceOf(Configuration::class, $annotatedTarget->getAttributeInstance());
    }

    public function injectNameProvider() : array {
        return [
            [0, 'foo'],
            [1, 'bar']
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
            [0, Inject::class],
            [1, Inject::class]
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
            [0, 'baz'],
            [1, 'baz'],
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
            [0, []],
            [1, []]
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