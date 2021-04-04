<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

use Cspray\AnnotatedInjector\DummyApps\SimpleServices;
use Cspray\AnnotatedInjector\DummyApps\InterfaceServiceSetup;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\AnnotatedInjector\AnnotatedInjectorFactory
 * @covers \Cspray\AnnotatedInjector\InjectorDefinitionCompiler
 * @covers \Cspray\AnnotatedInjector\Visitor\ServiceDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Visitor\ServiceSetupDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Interrogator\ServiceDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\Interrogator\ServiceSetupDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\ServiceDefinition
 * @covers \Cspray\AnnotatedInjector\AliasDefinition
 * @covers \Cspray\AnnotatedInjector\ServiceSetupDefinition
 */
class AnnotatedInjectorFactoryTest extends TestCase {

    public function testCreateSimpleServices() {
        $compiler = new InjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory(__DIR__ . '/DummyApps/SimpleServices', 'test');
        $injector = AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);
        $subject = $injector->make(SimpleServices\FooInterface::class);

        $this->assertInstanceOf(SimpleServices\FooImplementation::class, $subject);
    }

    public function testInterfaceServiceSetup() {
        $compiler = new InjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory(__DIR__ . '/DummyApps/InterfaceServiceSetup', 'test');
        $injector = AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

        $subject = $injector->make(InterfaceServiceSetup\FooInterface::class);

        $this->assertInstanceOf(InterfaceServiceSetup\FooImplementation::class, $subject);
        $this->assertEquals(1, $subject->getBarCounter());
    }

}