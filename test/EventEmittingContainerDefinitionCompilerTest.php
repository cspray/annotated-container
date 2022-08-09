<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Helper\StubAnnotatedContainerListener;
use Cspray\AnnotatedContainer\Helper\StubContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\Helper\TestLogger;
use Cspray\AnnotatedContainer\Internal\AfterCompileAnnotatedContainerEvent;
use Cspray\AnnotatedContainer\Internal\BeforeCompileAnnotatedContainerEvent;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class EventEmittingContainerDefinitionCompilerTest extends TestCase {

    public function testEmitCompileEvents() : void {
        $compileOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__)->build();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();

        $containerDefinitionCompiler = $this->getMockBuilder(ContainerDefinitionCompiler::class)->getMock();
        $containerDefinitionCompiler->expects($this->once())
            ->method('compile')
            ->with($compileOptions)
            ->willReturn($containerDefinition);

        $emitter = $this->getMockBuilder(AnnotatedContainerEmitter::class)->getMock();
        $emitter->expects($this->exactly(2))
            ->method('trigger')
            ->withConsecutive(
                [$this->isInstanceOf(BeforeCompileAnnotatedContainerEvent::class)],
                [$this->isInstanceOf(AfterCompileAnnotatedContainerEvent::class)]
            );

        $subject = new EventEmittingContainerDefinitionCompiler($containerDefinitionCompiler, $emitter);
        $subject->compile($compileOptions);
    }

    public function testLogEmitCompileEvents() : void {
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        $compiler = new StubContainerDefinitionCompiler($containerDefinition);
        $emitter = new StandardAnnotatedContainerEmitter();
        $emitter->registerListener(new StubAnnotatedContainerListener());
        $subject = new EventEmittingContainerDefinitionCompiler($compiler, $emitter);

        $logger = new TestLogger();
        $compileOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__)
            ->withLogger($logger)
            ->build();
        $subject->compile($compileOptions);

        $expected = [
            'message' => sprintf('Triggering %s listener with %s.', StubAnnotatedContainerListener::class, BeforeCompileAnnotatedContainerEvent::class),
            'context' => [
                'emitter' => $emitter::class,
                'listener' => StubAnnotatedContainerListener::class,
                'event' => BeforeCompileAnnotatedContainerEvent::class
            ]
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

}
