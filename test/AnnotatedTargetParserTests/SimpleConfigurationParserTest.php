<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetParserTests;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainerFixture\Fixtures;

class SimpleConfigurationParserTest extends AnnotatedTargetParserTestCase {

    protected function getDirectories() : array {
        return [Fixtures::configurationServices()->getPath()];
    }

    public function testCountExpectedTargets() {
        $this->assertCount(6, $this->targets);
    }

    public function testConfigurationReflectionTargetGetName() {
        $annotatedTarget = $this->targets[5];

        $this->assertSame(Fixtures::configurationServices()->myConfig()->getName(), $annotatedTarget->getTargetReflection()->getName());
    }

    public function testConfigurationReflectionAttributeGetName() {
        $annotatedTarget = $this->targets[5];

        $this->assertSame(Configuration::class, $annotatedTarget->getAttributeReflection()->getName());
    }

    public function testConfigurationReflectionAttributeInstanceOf() {
        $annotatedTarget = $this->targets[5];

        $this->assertInstanceOf(Configuration::class, $annotatedTarget->getAttributeInstance());
    }

    public function injectNameProvider() : array {
        return [
            [0, 'key'],
            [1, 'port'],
            [2, 'user'],
            [3, 'testMode'],
            [4, 'testMode']
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
            [1, Inject::class],
            [2, Inject::class],
            [3, Inject::class],
            [4, Inject::class]
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
            [0, 'my-api-key'],
            [1, 1234],
            [2, 'USER'],
            [3, true],
            [4, false]
        ];
    }

    /**
     * @dataProvider injectValueProvider
     */
    public function testInjectReflectionAttributeInstanceValue(int $index, string|int|bool $value) {
        $annotatedTarget = $this->targets[$index];

        $this->assertSame($value, $annotatedTarget->getAttributeInstance()->value);
    }

    public function injectProfilesProvider() : array {
        return [
            [0, []],
            [1, []],
            [2, []],
            [3, ['dev', 'test']],
            [4, ['prod']]
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