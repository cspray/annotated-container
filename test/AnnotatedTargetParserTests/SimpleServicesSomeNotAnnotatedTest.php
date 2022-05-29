<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetParserTests;

use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainerFixture\Fixtures;

class SimpleServicesSomeNotAnnotatedTest extends AnnotatedTargetParserTestCase {

    protected function getDirectories(): array {
        return [Fixtures::nonAnnotatedServices()->getPath()];
    }

    public function testAssertCount() {
        $this->assertCount(1, $this->targets);
    }
}