<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

use Cspray\AnnotatedInjector\DummyApps\SimpleServices;
use Cspray\AnnotatedInjector\DummyApps\InterfaceServicePrepare;
use Cspray\AnnotatedInjector\DummyApps\InjectorExecuteServicePrepare;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\AnnotatedInjector\AnnotatedInjectorFactory
 * @covers \Cspray\AnnotatedInjector\InjectorDefinitionCompiler
 * @covers \Cspray\AnnotatedInjector\Visitor\ServiceDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Visitor\ServicePrepareDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Interrogator\ServiceDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\Interrogator\ServicePrepareDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\ServiceDefinition
 * @covers \Cspray\AnnotatedInjector\AliasDefinition
 * @covers \Cspray\AnnotatedInjector\ServicePrepareDefinition
 */
class AnnotatedInjectorFactoryTest extends TestCase {

    public function testCreateSimpleServices() {
        $compiler = new InjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory(__DIR__ . '/DummyApps/SimpleServices', 'test');
        $injector = AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);
        $subject = $injector->make(SimpleServices\FooInterface::class);

        $this->assertInstanceOf(SimpleServices\FooImplementation::class, $subject);
    }

    public function testInterfaceServicePrepare() {
        $compiler = new InjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory(__DIR__ . '/DummyApps/InterfaceServicePrepare', 'test');
        $injector = AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

        $subject = $injector->make(InterfaceServicePrepare\FooInterface::class);

        $this->assertInstanceOf(InterfaceServicePrepare\FooImplementation::class, $subject);
        $this->assertEquals(1, $subject->getBarCounter());
    }

    public function testServicePrepareInvokedOnInjector() {
        $compiler = new InjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory(__DIR__ . '/DummyApps/InjectorExecuteServicePrepare', 'test');
        $injector = AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

        $subject = $injector->make(InjectorExecuteServicePrepare\FooInterface::class);

        $this->assertInstanceOf(InjectorExecuteServicePrepare\FooImplementation::class, $subject);
        $this->assertInstanceOf(InjectorExecuteServicePrepare\BarImplementation::class, $subject->getBar());
    }

}