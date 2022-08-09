<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Helper\StubAnnotatedContainerListener;
use Cspray\AnnotatedContainer\Helper\StubContainerFactory;
use Cspray\AnnotatedContainer\Helper\StubParameterStore;
use Cspray\AnnotatedContainer\Helper\TestLogger;
use Cspray\AnnotatedContainer\Internal\AfterContainerCreationAnnotatedContainerEvent;
use Cspray\AnnotatedContainer\Internal\BeforeContainerCreationAnnotatedContainerEvent;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class EventEmittingContainerFactoryTest extends TestCase {

    public function testDelegateAddParameterStore() : void {
        $parameterStore = new StubParameterStore();
        $containerFactory = $this->getMockBuilder(ContainerFactory::class)->getMock();
        $containerFactory->expects($this->once())
            ->method('addParameterStore')
            ->with($parameterStore);
        $emitter = $this->getMockBuilder(AnnotatedContainerEmitter::class)->getMock();

        $subject = new EventEmittingContainerFactory($containerFactory, $emitter);

        $subject->addParameterStore($parameterStore);
    }

    public function testCreateContainerEmitsCorrectEvents() : void {
        $annotatedContainer = $this->getMockBuilder(AnnotatedContainer::class)->getMock();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        $containerFactory = $this->getMockBuilder(ContainerFactory::class)->getMock();
        $containerFactory->expects($this->once())
            ->method('createContainer')
            ->with($containerDefinition, null)
            ->willReturn($annotatedContainer);

        $emitter = $this->getMockBuilder(AnnotatedContainerEmitter::class)->getMock();
        $emitter->expects($this->exactly(2))
            ->method('trigger')
            ->withConsecutive(
                [$this->isInstanceOf(BeforeContainerCreationAnnotatedContainerEvent::class)],
                [$this->isInstanceOf(AfterContainerCreationAnnotatedContainerEvent::class)]
            );

        $subject = new EventEmittingContainerFactory($containerFactory, $emitter);
        $subject->createContainer($containerDefinition);
    }

    public function testLogCreateContainerEvents() : void {
        $annotatedContainer = $this->getMockBuilder(AnnotatedContainer::class)->getMock();
        $containerFactory = new StubContainerFactory($annotatedContainer);
        $emitter = new StandardAnnotatedContainerEmitter();
        $emitter->registerListener(new StubAnnotatedContainerListener());

        $subject = new EventEmittingContainerFactory(
            $containerFactory,
            $emitter
        );

        $logger = new TestLogger();
        $subject->createContainer(
            $this->getMockBuilder(ContainerDefinition::class)->getMock(),
            ContainerFactoryOptionsBuilder::forActiveProfiles('default')
                ->withLogger($logger)
                ->build()
        );

        $expected = [
            'message' => sprintf(
                'Triggering %s listener with %s.',
                StubAnnotatedContainerListener::class,
                BeforeContainerCreationAnnotatedContainerEvent::class
            ),
            'context' => [
                'emitter' => $emitter::class,
                'listener' => StubAnnotatedContainerListener::class,
                'event' => BeforeContainerCreationAnnotatedContainerEvent::class
            ]
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

}