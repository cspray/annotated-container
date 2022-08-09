<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\AnnotatedContainerLifecycle;
use PHPUnit\Framework\TestCase;

final class AfterContainerCreationAnnotatedContainerEventTest extends TestCase {

    public function testGetLifecycle() : void {
        $event = new AfterContainerCreationAnnotatedContainerEvent(
            $this->getMockBuilder(AnnotatedContainer::class)->getMock()
        );

        self::assertSame(
            AnnotatedContainerLifecycle::AfterContainerCreation,
            $event->getLifecycle()
        );
    }

    public function testGetTarget() : void {
        $event = new AfterContainerCreationAnnotatedContainerEvent(
            $container = $this->getMockBuilder(AnnotatedContainer::class)->getMock()
        );

        self::assertSame(
            $container,
            $event->getTarget()
        );
    }

}