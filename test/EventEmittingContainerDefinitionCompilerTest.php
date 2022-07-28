<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Internal\AfterCompileAnnotatedContainerEvent;
use Cspray\AnnotatedContainer\Internal\BeforeCompileAnnotatedContainerEvent;
use PHPUnit\Framework\TestCase;

class EventEmittingContainerDefinitionCompilerTest extends TestCase {

    public function testEmitBeforeCompileEvent() : void {
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

}