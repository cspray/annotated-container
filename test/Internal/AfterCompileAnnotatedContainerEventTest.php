<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\AnnotatedContainerLifecycle;
use Cspray\AnnotatedContainer\ContainerDefinition;
use PHPUnit\Framework\TestCase;

final class AfterCompileAnnotatedContainerEventTest extends TestCase {

    public function testGetLifecycle() : void {
        $event = new AfterCompileAnnotatedContainerEvent(
            $this->getMockBuilder(ContainerDefinition::class)->getMock()
        );

        self::assertSame(
            AnnotatedContainerLifecycle::AfterCompile,
            $event->getLifecycle()
        );
    }

    public function testGetContainerDefinition() : void {
        $event = new AfterCompileAnnotatedContainerEvent(
            $definition = $this->getMockBuilder(ContainerDefinition::class)->getMock()
        );

        self::assertSame(
            $definition,
            $event->getTarget()
        );
    }

}