<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\AnnotatedContainerLifecycle;
use Cspray\AnnotatedContainer\ContainerDefinition;
use PHPUnit\Framework\TestCase;

final class BeforeContainerCreationAnnotatedContainerEventTest extends TestCase {

    public function testGetLifecycle() : void {
        $event = new BeforeContainerCreationAnnotatedContainerEvent(
            $this->getMockBuilder(ContainerDefinition::class)->getMock()
        );

        self::assertSame(
            AnnotatedContainerLifecycle::BeforeContainerCreation,
            $event->getLifecycle()
        );
    }

    public function testGetTarget() : void {
        $event = new BeforeContainerCreationAnnotatedContainerEvent(
            $definition = $this->getMockBuilder(ContainerDefinition::class)->getMock()
        );

        self::assertSame(
            $definition,
            $event->getTarget()
        );
    }

}