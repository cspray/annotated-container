<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\AnnotatedContainerLifecycle;
use PHPUnit\Framework\TestCase;

final class BeforeCompileAnnotatedContainerEventTest extends TestCase {

    public function testGetEventLifecycle() : void {
        $event = new BeforeCompileAnnotatedContainerEvent();

        self::assertSame(
            AnnotatedContainerLifecycle::BeforeCompile,
            $event->getLifecycle()
        );
    }

    public function testGetTargetIsNull() : void {
        $event = new BeforeCompileAnnotatedContainerEvent();

        self::assertNull($event->getTarget());
    }

}