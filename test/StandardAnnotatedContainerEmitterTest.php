<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Helper\StubAnnotatedContainerListener;
use Cspray\AnnotatedContainer\Helper\TestLogger;
use Cspray\AnnotatedContainer\Internal\AfterCompileAnnotatedContainerEvent;
use Cspray\AnnotatedContainer\Internal\AfterContainerCreationAnnotatedContainerEvent;
use Cspray\AnnotatedContainer\Internal\BeforeCompileAnnotatedContainerEvent;
use Cspray\AnnotatedContainer\Internal\BeforeContainerCreationAnnotatedContainerEvent;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

final class StandardAnnotatedContainerEmitterTest extends TestCase {

    public function lifecycleProvider() : array {
        return [
            [new BeforeCompileAnnotatedContainerEvent()],
            [new AfterCompileAnnotatedContainerEvent($this->getMockBuilder(ContainerDefinition::class)->getMock())],
            [new BeforeContainerCreationAnnotatedContainerEvent($this->getMockBuilder(ContainerDefinition::class)->getMock())],
            [new AfterContainerCreationAnnotatedContainerEvent($this->getMockBuilder(AnnotatedContainer::class)->getMock())],
        ];
    }

    /**
     * @dataProvider lifecycleProvider
     */
    public function testTriggerRegisteredListenersWithBeforeCompileEvent(AnnotatedContainerEvent $event) : void {
        $subject = new StandardAnnotatedContainerEmitter();

        $listener1 = $this->getMockBuilder(AnnotatedContainerListener::class)->getMock();
        $listener1->expects($this->once())
            ->method('handle')
            ->with($event);

        $listener2 = $this->getMockBuilder(AnnotatedContainerListener::class)->getMock();
        $listener2->expects($this->once())
            ->method('handle')
            ->with($event);

        $listener3 = $this->getMockBuilder(AnnotatedContainerListener::class)->getMock();
        $listener3->expects($this->once())
            ->method('handle')
            ->with($event);

        $subject->registerListener($listener1);
        $subject->registerListener($listener2);
        $subject->registerListener($listener3);

        $subject->trigger($event);
    }

    public function testLogRegisteringListener() : void {
        $logger = new TestLogger();
        $subject = new StandardAnnotatedContainerEmitter();
        $subject->setLogger($logger);

        $listener = new StubAnnotatedContainerListener();
        $subject->registerListener($listener);

        $expected = [
            'message' => sprintf('Registering listener %s.', $listener::class),
            'context' => [
                'emitter' => $subject::class,
                'listener' => $listener::class
            ]
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLogTriggeringListeners() : void {
        $logger = new TestLogger();
        $subject = new StandardAnnotatedContainerEmitter();
        $subject->setLogger($logger);

        $listener = new StubAnnotatedContainerListener();
        $subject->registerListener($listener);

        $event = new BeforeCompileAnnotatedContainerEvent();
        $subject->trigger($event);

        $expected = [
            'message' => sprintf(
                'Triggering %s listener with %s.',
                StubAnnotatedContainerListener::class,
                BeforeCompileAnnotatedContainerEvent::class
            ),
            'context' => [
                'emitter' => $subject::class,
                'listener' => $listener::class,
                'event' => $event::class
            ]
        ];

        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

}
