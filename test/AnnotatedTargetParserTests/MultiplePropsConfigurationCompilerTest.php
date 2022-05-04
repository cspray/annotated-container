<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetCompilerTests;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\DummyApps;

class MultiplePropsConfigurationCompilerTest extends AnnotatedTargetCompilerTestCase {

    protected function getDirectories() : array {
        return [DummyAppUtils::getRootDir() . '/MultiplePropsConfiguration'];
    }

    public function testCountExpectedTargets() {
        $this->assertCount(3, $this->provider->getTargets());
    }

    public function testConfigurationReflectionTargetGetName() {
        $annotatedTarget = $this->provider->getTargets()[2];

        $this->assertSame(DummyApps\MultiplePropsConfiguration\MyConfig::class, $annotatedTarget->getTargetReflection()->getName());
    }

    public function testConfigurationReflectionAttributeGetName() {
        $annotatedTarget = $this->provider->getTargets()[2];

        $this->assertSame(Configuration::class, $annotatedTarget->getAttributeReflection()->getName());
    }

    public function testConfigurationReflectionAttributeInstanceOf() {
        $annotatedTarget = $this->provider->getTargets()[2];

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
        $annotatedTarget = $this->provider->getTargets()[$index];

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
        $annotatedTarget = $this->provider->getTargets()[$index];

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
        $annotatedTarget = $this->provider->getTargets()[$index];

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
        $annotatedTarget = $this->provider->getTargets()[$index];
        $this->assertSame($profiles, $annotatedTarget->getAttributeInstance()->profiles);
    }

}