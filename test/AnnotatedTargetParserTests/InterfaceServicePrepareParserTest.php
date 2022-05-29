<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetParserTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\DummyApps;
use Cspray\AnnotatedContainerFixture\Fixtures;

class InterfaceServicePrepareParserTest extends AnnotatedTargetParserTestCase {

    protected function getDirectories(): array {
        return [Fixtures::interfacePrepareServices()->getPath()];
    }

    private function getExpectedTarget() : ?AnnotatedTarget {
        return $this->getAnnotatedTargetForTargetReflectionMethod(
            Fixtures::interfacePrepareServices()->fooInterface()->getName(),
            'setBar'
        );
    }

    public function testHasCorrectCount() {
        $this->assertCount(3, $this->targets);
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