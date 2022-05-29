<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetParserTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\DummyApps;
use Cspray\AnnotatedContainerFixture\Fixtures;
use ReflectionClass;

class SingleConcreteServiceParserTest extends AnnotatedTargetParserTestCase {

    protected function getDirectories() : array {
        return [
            Fixtures::singleConcreteService()->getPath()
        ];
    }

    public function testHasCorrectAnnotatedTargetCount() {
        $this->assertCount(1, $this->targets);
    }

    public function testAnnotatedTargetsAreCorrectType() {
        $annotatedTargets = $this->targets;
        $this->assertInstanceOf(AnnotatedTarget::class, $annotatedTargets[0]);
    }

    public function testAnnotatedTargetsHaveNonNullTargetReflection() {
        $annotatedTargets = $this->targets;
        $this->assertInstanceOf(ReflectionClass::class, $annotatedTargets[0]->getTargetReflection());
    }

    public function testAnnotatedTargetsHaveFooInterfaceTargetReflection() {
        $class = Fixtures::singleConcreteService()->fooImplementation()->getName();
        $target = $this->getAnnotatedTargetForTargetReflectionClass($class);
        $this->assertNotNull($target);
    }

    public function testAnnotatedTargetsHaveFooImplementationTargetReflection() {
        $class = Fixtures::singleConcreteService()->fooImplementation()->getName();
        $target = $this->getAnnotatedTargetForTargetReflectionClass($class);
        $this->assertNotNull($target);
    }

    public function testAnnotatedTargetsHaveServiceReflectionAttribute() {
        $targets = $this->targets;
        $this->assertSame(Service::class, $targets[0]->getAttributeReflection()->getName());
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
    }

    public function testAnnotatedTargetAttributeInstanceSameObject() {
        $targets = $this->targets;
        $this->assertSame($targets[0]->getAttributeInstance(), $targets[0]->getAttributeInstance());
    }

}