<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetParserTests;

use Cspray\AnnotatedContainer\AnnotatedTarget;
use Cspray\AnnotatedContainer\AnnotatedTargetType;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\DummyApps;

class ServiceDelegateTest extends AnnotatedTargetParserTestCase {

    protected function getDirectories(): array {
        return [DummyAppUtils::getRootDir() . '/ServiceDelegate'];
    }

    private function getExpectedTarget() : ?AnnotatedTarget {
        return $this->getAnnotatedTargetForTargetReflectionMethod(
            DummyApps\ServiceDelegate\ServiceFactory::class,
            'createService'
        );
    }

    public function testHasCorrectAnnotatedTargetCount() {
        $this->assertCount(3, $this->targets);
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