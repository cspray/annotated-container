<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetParserTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\DummyApps;
use ReflectionClass;

class SimpleServicesParserTest extends AnnotatedTargetParserTestCase {

    protected function getDirectories() : array {
        return [DummyAppUtils::getRootDir() . '/SimpleServices'];
    }

    public function testHasCorrectAnnotatedTargetCount() {
        $this->assertCount(2, $this->targets);
    }

    public function testAnnotatedTargetsAreCorrectType() {
        $annotatedTargets = $this->targets;
        $this->assertInstanceOf(AnnotatedTarget::class, $annotatedTargets[0]);
        $this->assertInstanceOf(AnnotatedTarget::class, $annotatedTargets[1]);
    }

    public function testAnnotatedTargetsHaveNonNullTargetReflection() {
        $annotatedTargets = $this->targets;
        $this->assertInstanceOf(ReflectionClass::class, $annotatedTargets[0]->getTargetReflection());
        $this->assertInstanceOf(ReflectionClass::class, $annotatedTargets[0]->getTargetReflection());
    }

    public function testAnnotatedTargetsHaveFooInterfaceTargetReflection() {
        $target = $this->getAnnotatedTargetForTargetReflectionClass(DummyApps\SimpleServices\FooInterface::class);
        $this->assertNotNull($target);
    }

    public function testAnnotatedTargetsHaveFooImplementationTargetReflection() {
        $target = $this->getAnnotatedTargetForTargetReflectionClass(DummyApps\SimpleServices\FooImplementation::class);
        $this->assertNotNull($target);
    }

    public function testAnnotatedTargetsHaveServiceReflectionAttribute() {
        $targets = $this->targets;
        $this->assertSame(Service::class, $targets[0]->getAttributeReflection()->getName());
        $this->assertSame(Service::class, $targets[1]->getAttributeReflection()->getName());
    }

    public function testAnnotatedTargetHasSameTargetReflection() {
        $targets = $this->targets;
        $this->assertSame($targets[0]->getTargetReflection(), $targets[0]->getTargetReflection());
    }

    public function testAnnotatedTargetHasSameAttributeReflection() {
        $targets = $this->targets;
        $this->assertSame($targets[0]->getAttributeReflection(), $targets[0]->getAttributeReflection());
    }

    public function testAnnotatedTargetGetAttributeInstance() {
        $targets = $this->targets;
        $this->assertInstanceOf(Service::class, $targets[0]->getAttributeInstance());
        $this->assertInstanceOf(Service::class, $targets[1]->getAttributeInstance());
    }

    public function testAnnotatedTargetAttributeInstanceSameObject() {
        $targets = $this->targets;
        $this->assertSame($targets[0]->getAttributeInstance(), $targets[0]->getAttributeInstance());
    }

}