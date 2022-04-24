<?php

namespace Cspray\AnnotatedContainer\AnnotatedTargetCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\AnnotatedTargetType;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\DummyApps;
use ReflectionClass;

class SimpleServicesCompilerTest extends AnnotatedTargetCompilerTestCase {

    protected function getDirectories() : array {
        return [DummyAppUtils::getRootDir() . '/SimpleServices'];
    }

    public function testHasCorrectAnnotatedTargetCount() {
        $this->assertCount(2, $this->provider->getTargets());
    }

    public function testAnnotatedTargetsAreCorrectType() {
        $annotatedTargets = $this->provider->getTargets();
        $this->assertInstanceOf(AnnotatedTarget::class, $annotatedTargets[0]);
        $this->assertInstanceOf(AnnotatedTarget::class, $annotatedTargets[1]);
    }

    public function testAnnotatedTargetsHaveCorrectTargetType() {
        $annotatedTargets = $this->provider->getTargets();
        $this->assertSame(AnnotatedTargetType::ClassTarget, $annotatedTargets[0]->getTargetType());
        $this->assertSame(AnnotatedTargetType::ClassTarget, $annotatedTargets[1]->getTargetType());
    }

    public function testAnnotatedTargetsHaveNonNullTargetReflection() {
        $annotatedTargets = $this->provider->getTargets();
        $this->assertInstanceOf(ReflectionClass::class, $annotatedTargets[0]->getTargetReflection());
        $this->assertInstanceOf(ReflectionClass::class, $annotatedTargets[0]->getTargetReflection());
    }

    public function testAnnotatedTargetsHaveFooInterfaceTargetReflection() {
        $target = $this->getAnnotatedTargetForTargetReflectionClass($this->provider, DummyApps\SimpleServices\FooInterface::class);
        $this->assertNotNull($target);
    }

    public function testAnnotatedTargetsHaveFooImplementationTargetReflection() {
        $target = $this->getAnnotatedTargetForTargetReflectionClass($this->provider, DummyApps\SimpleServices\FooImplementation::class);
        $this->assertNotNull($target);
    }

    public function testAnnotatedTargetsHaveServiceReflectionAttribute() {
        $targets = $this->provider->getTargets();
        $this->assertSame(Service::class, $targets[0]->getAttributeReflection()->getName());
        $this->assertSame(Service::class, $targets[1]->getAttributeReflection()->getName());
    }

    public function testAnnotatedTargetHasSameTargetReflection() {
        $targets = $this->provider->getTargets();
        $this->assertSame($targets[0]->getTargetReflection(), $targets[0]->getTargetReflection());
    }

    public function testAnnotatedTargetHasSameAttributeReflection() {
        $targets = $this->provider->getTargets();
        $this->assertSame($targets[0]->getAttributeReflection(), $targets[0]->getAttributeReflection());
    }

    public function testAnnotatedTargetGetAttributeInstance() {
        $targets = $this->provider->getTargets();
        $this->assertInstanceOf(Service::class, $targets[0]->getAttributeInstance());
        $this->assertInstanceOf(Service::class, $targets[1]->getAttributeInstance());
    }

    public function testAnnotatedTargetAttributeInstanceSameObject() {
        $targets = $this->provider->getTargets();
        $this->assertSame($targets[0]->getAttributeInstance(), $targets[0]->getAttributeInstance());
    }




}