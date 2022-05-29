<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetParserTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\DummyApps;
use Cspray\AnnotatedContainerFixture\Fixtures;

class InjectIntMethodParamTest extends AnnotatedTargetParserTestCase {

    protected function getDirectories(): array {
        return [Fixtures::injectConstructorServices()->getPath()];
    }

    /**
     * @return AnnotatedTarget[]
     */
    private function getExpectedTargets() : array {
        return $this->getAnnotatedTargetsForTargetReflectParameter(
              Fixtures::injectConstructorServices()->injectIntService()->getName(),
            '__construct',
            'meaningOfLife'
        );
    }

    public function testTargetCount() {
        $this->assertCount(24, $this->targets);
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