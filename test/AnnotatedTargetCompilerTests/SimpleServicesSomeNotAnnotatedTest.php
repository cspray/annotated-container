<?php

namespace Cspray\AnnotatedContainer\AnnotatedTargetCompilerTests;

use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;

class SimpleServicesSomeNotAnnotatedTest extends AnnotatedTargetCompilerTestCase {

    protected function getDirectories(): array {
        return [DummyAppUtils::getRootDir() . '/SimpleServicesSomeNotAnnotated'];
    }

    public function testAssertCount() {
        $this->assertCount(2, $this->provider->getTargets());
    }
}