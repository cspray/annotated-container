<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetCompilerTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\AnnotatedTargetType;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\DummyApps;

class ServiceDelegateTest extends AnnotatedTargetCompilerTestCase {

    protected function getDirectories(): array {
        return [DummyAppUtils::getRootDir() . '/ServiceDelegate'];
    }

    private function getExpectedTarget() : ?AnnotatedTarget {
        return $this->getAnnotatedTargetForTargetReflectionMethod(
            $this->provider,
            DummyApps\ServiceDelegate\ServiceFactory::class,
            'createService'
        );
    }

    public function testHasCorrectAnnotatedTargetCount() {
        $consumer = $this->compileDirectories();

        $this->assertCount(3, $consumer->getTargets());
    }

    public function testCorrectTargetPresent() {
        $this->assertNotNull($this->getExpectedTarget());
    }

    public function testCorrectAttributeReflectionType() {
        $this->assertSame(ServiceDelegate::class, $this->getExpectedTarget()->getAttributeReflection()->getName());
    }

    public function testTargetReflectionHasSameObject() {
        $this->assertSame($this->getExpectedTarget()->getTargetReflection(), $this->getExpectedTarget()->getTargetReflection());
    }

    public function testAttributeReflectionHasSameObject() {
        $this->assertSame($this->getExpectedTarget()->getAttributeReflection(), $this->getExpectedTarget()->getAttributeReflection());
    }

    public function testAttributeInstanceHasSameObject() {
        $this->assertSame($this->getExpectedTarget()->getAttributeInstance(), $this->getExpectedTarget()->getAttributeInstance());
    }


}