<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Helper\StubAnnotatedContainerListener;
use Cspray\AnnotatedContainer\Internal\AfterCompileAnnotatedContainerEvent;
use Cspray\AnnotatedContainer\Internal\BeforeCompileAnnotatedContainerEvent;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;

final class ContainerDefinitionCompilerBuilderTest extends TestCase {

    private VirtualDirectory $vfs;

    protected function setUp() : void {
        $this->vfs = VirtualFilesystem::setup();
    }

    public function testInstanceWithoutCache() : void {
        $compiler = ContainerDefinitionCompilerBuilder::withoutCache()->build();

        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build()
        );

        self::assertCount(1, $containerDefinition->getServiceDefinitions());
    }

    public function testInstanceWithCache() : void {
        $compiler = ContainerDefinitionCompilerBuilder::withCache('vfs://root')->build();

        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build()
        );

        self::assertCount(1, $containerDefinition->getServiceDefinitions());
        self::assertFileExists('vfs://root/' . md5(Fixtures::singleConcreteService()->getPath()));
    }

    public function testWithEventListenerImmutable() : void {
        $compilerBuilder = ContainerDefinitionCompilerBuilder::withoutCache();
        $builderWithEvents = $compilerBuilder->withEventListener(new StubAnnotatedContainerListener());

        self::assertNotSame($compilerBuilder, $builderWithEvents);
    }

    public function testWithEventListenersAreTriggered() : void {
       $compiler = ContainerDefinitionCompilerBuilder::withoutCache()
           ->withEventListener($listener1 = new StubAnnotatedContainerListener())
           ->withEventListener($listener2 = new StubAnnotatedContainerListener())
           ->build();

       $containerDefinition = $compiler->compile(
           ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build()
       );

       self::assertCount(1, $containerDefinition->getServiceDefinitions());

       self::assertCount(2, $listener1->getEvents());
       self::assertCount(2, $listener2->getEvents());

       self::assertInstanceOf(BeforeCompileAnnotatedContainerEvent::class, $listener1->getEvents()[0]);
       self::assertInstanceOf(BeforeCompileAnnotatedContainerEvent::class, $listener1->getEvents()[0]);

       self::assertInstanceOf(AfterCompileAnnotatedContainerEvent::class, $listener1->getEvents()[1]);
       self::assertInstanceOf(AfterCompileAnnotatedContainerEvent::class, $listener2->getEvents()[1]);

       self::assertSame($containerDefinition, $listener1->getEvents()[1]->getTarget());
       self::assertSame($containerDefinition, $listener2->getEvents()[1]->getTarget());
    }

    public function testCompilerBuilderEmitsEventsRegisteredInGlobalEventEmitter() : void {
        eventEmitter()->registerListener($listener = new StubAnnotatedContainerListener());
        compiler()->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())
                ->build()
        );

        self::assertCount(2, $listener->getEvents());
    }

}