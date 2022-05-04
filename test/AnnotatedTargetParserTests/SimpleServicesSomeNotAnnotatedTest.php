<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetParserTests;

use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;

class SimpleServicesSomeNotAnnotatedTest extends AnnotatedTargetParserTestCase {

    protected function getDirectories(): array {
        return [DummyAppUtils::getRootDir() . '/SimpleServicesSomeNotAnnotated'];
    }

    public function testAssertCount() {
        $this->assertCount(2, $this->targets);
    }
}