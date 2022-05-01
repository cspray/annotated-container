<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\DummyApps;

class InjectIntMethodParamTest extends AnnotatedTargetCompilerTestCase {

    protected function getDirectories(): array {
        return [DummyAppUtils::getRootDir() . '/InjectIntMethodParam'];
    }

    /**
     * @return AnnotatedTarget[]
     */
    private function getExpectedTargets() : array {
        return $this->getAnnotatedTargetsForTargetReflectParameter(
            $this->provider,
              DummyApps\InjectIntMethodParam\FooImplementation::class,
            'setSomething',
            'value'
        );
    }

    public function testTargetCount() {
        $this->assertCount(3, $this->provider->getTargets());
    }

    public function testExpectedTargetCount() {
        $this->assertCount(1, $this->getExpectedTargets());
    }

    public function testGetAttributeReflection() {
        $this->assertSame(Inject::class, $this->getExpectedTargets()[0]->getAttributeReflection()->getName());
    }

    public function testGetAttributeInstanceCorrectInstanceOf() {
        $this->assertInstanceOf(Inject::class, $this->getExpectedTargets()[0]->getAttributeInstance());
    }

    public function testGetAttributeInstanceValue() {
        $this->assertSame(42, $this->getExpectedTargets()[0]->getAttributeInstance()->value);
    }

}