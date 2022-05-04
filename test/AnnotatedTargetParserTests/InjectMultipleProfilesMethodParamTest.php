<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetParserTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\DummyApps;

class InjectMultipleProfilesMethodParamTest extends AnnotatedTargetParserTestCase {

    protected function getDirectories(): array {
        return [DummyAppUtils::getRootDir() . '/InjectMultipleProfilesMethodParam'];
    }

    /**
     * @return AnnotatedTarget[]
     */
    private function getExpectedTargets() : array {
        return $this->getAnnotatedTargetsForTargetReflectParameter(
            DummyApps\InjectMultipleProfilesMethodParam\FooImplementation::class,
            '__construct',
            'stringParam'
        );
    }

    public function testTargetCount() {
        $this->assertCount(4, $this->targets);
    }

    public function testExpectedTargetCount() {
        $this->assertCount(3, $this->getExpectedTargets());
    }

    public function indexProvider() : array {
        return [[0], [1], [2]];
    }

    /**
     * @dataProvider indexProvider
     */
    public function testExpectedTargetReflectionDeclaringClass(int $index) {
        $target = $this->getExpectedTargets()[$index];
        $actual = $target->getTargetReflection()->getDeclaringClass()->getName();
        $this->assertSame(DummyApps\InjectMultipleProfilesMethodParam\FooImplementation::class, $actual);
    }

    /**
     * @dataProvider indexProvider
     */
    public function testExpectedTargetReflectionDeclaringFunction(int $index) {
        $target = $this->getExpectedTargets()[$index];
        $actual = $target->getTargetReflection()->getDeclaringFunction()->getName();
        $this->assertSame('__construct', $actual);
    }

    /**
     * @dataProvider indexProvider
     */
    public function testExpectedTargetReflectionName(int $index) {
        $target = $this->getExpectedTargets()[$index];
        $actual = $target->getTargetReflection()->getName();
        $this->assertSame('stringParam', $actual);
    }

    /**
     * @dataProvider indexProvider
     */
    public function testExpectedAttributeReflectionType(int $index) {
        $this->assertSame(Inject::class, $this->getExpectedTargets()[$index]->getAttributeReflection()->getName());
    }

    public function indexValueProvider() : array {
        return [
            [0, 'from-dev'],
            [1, 'from-test'],
            [2, 'from-prod']
        ];
    }

    /**
     * @dataProvider indexValueProvider
     */
    public function testExpectedAttributeReflectionInstanceValue(int $index, string $expected) {
        $this->assertSame($expected, $this->getExpectedTargets()[$index]->getAttributeInstance()->value);
    }

    public function indexProfilesProvider() : array {
        return [
            [0, ['dev']],
            [1, ['test']],
            [2, ['prod']]
        ];
    }

    /**
     * @dataProvider indexProfilesProvider
     */
    public function testExpectedAttributeReflectionInstanceProfiles(int $index, array $expected) {
        $this->assertSame($expected, $this->getExpectedTargets()[$index]->getAttributeInstance()->profiles);
    }
}