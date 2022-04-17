<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\DummyApps;

class InterfaceServicePrepareCompilerTest extends AnnotatedTargetCompilerTestCase {

    protected function getDirectories(): array {
        return [DummyAppUtils::getRootDir() . '/InterfaceServicePrepare'];
    }

    private function getExpectedTarget() : ?AnnotatedTarget {
        return $this->getAnnotatedTargetForTargetReflectionMethod(
            $this->provider,
            DummyApps\InterfaceServicePrepare\FooInterface::class,
            'setBar'
        );
    }

    public function testHasCorrectCount() {
        $this->assertCount(3, $this->provider->getTargets());
    }

    public function testHasCorrectServicePrepareMethod() {
        $this->assertNotNull($this->getExpectedTarget());
    }

    public function testGetAttributeReflectionType() {
        $this->assertSame(
            ServicePrepare::class,
            $this->getExpectedTarget()->getAttributeReflection()->getName()
        );
    }

    public function testGetAttributeInstance() {
        $this->assertInstanceOf(
            ServicePrepare::class,
            $this->getExpectedTarget()->getAttributeInstance()
        );
    }

    public function testGetTargetReflectionHasSameObject() {
        $this->assertSame(
            $this->getExpectedTarget()->getTargetReflection(),
            $this->getExpectedTarget()->getTargetReflection()
        );
    }

    public function testGetAttributeReflectionSameObject() {
        $this->assertSame(
            $this->getExpectedTarget()->getAttributeReflection(),
            $this->getExpectedTarget()->getAttributeReflection()
        );
    }

    public function testGetAttributeInstanceSameObject() {
        $this->assertSame(
            $this->getExpectedTarget()->getAttributeInstance(),
            $this->getExpectedTarget()->getAttributeInstance()
        );
    }

}