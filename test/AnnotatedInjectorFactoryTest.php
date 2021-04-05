<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

use Auryn\InjectionException;
use Cspray\AnnotatedInjector\DummyApps\SimpleServices;
use Cspray\AnnotatedInjector\DummyApps\InterfaceServicePrepare;
use Cspray\AnnotatedInjector\DummyApps\InjectorExecuteServicePrepare;
use Cspray\AnnotatedInjector\DummyApps\SimpleDefineScalar;
use Cspray\AnnotatedInjector\DummyApps\MultipleDefineScalars;
use Cspray\AnnotatedInjector\DummyApps\ConstantDefineScalar;
use Cspray\AnnotatedInjector\DummyApps\SimpleDefineScalarFromEnv;
use Cspray\AnnotatedInjector\DummyApps\SimpleDefineService;
use Cspray\AnnotatedInjector\DummyApps\MultipleAliasResolution;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\AnnotatedInjector\AnnotatedInjectorFactory
 * @covers \Cspray\AnnotatedInjector\InjectorDefinitionCompiler
 * @covers \Cspray\AnnotatedInjector\Visitor\ServiceDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Visitor\ServicePrepareDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Visitor\DefineScalarDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Interrogator\ServiceDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\Interrogator\ServicePrepareDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\Interrogator\DefineScalarDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\ServiceDefinition
 * @covers \Cspray\AnnotatedInjector\AliasDefinition
 * @covers \Cspray\AnnotatedInjector\ServicePrepareDefinition
 * @covers \Cspray\AnnotatedInjector\DefineScalarDefinition
 * @covers \Cspray\AnnotatedInjector\Visitor\AbstractNodeVisitor
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

    public function testSimpleDefineScalar() {
        $compiler = new InjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory(__DIR__ . '/DummyApps/SimpleDefineScalar', 'test');
        $injector = AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

        $subject = $injector->make(SimpleDefineScalar\FooImplementation::class);

        $this->assertSame('string param test value', $subject->stringParam);
        $this->assertSame(42, $subject->intParam);
        $this->assertSame(42.0, $subject->floatParam);
        $this->assertTrue($subject->boolParam);
    }

    public function testMultipleDefineScalars() {
        $compiler = new InjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory(__DIR__ . '/DummyApps/MultipleDefineScalars', 'test');
        $injector = AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

        $subject = $injector->make(MultipleDefineScalars\FooImplementation::class);

        $this->assertSame('constructor param', $subject->stringParam);
        $this->assertSame('prepare param', $subject->prepareParam);
    }

    public function testConstantDefineScalar() {
        // we need to make sure this file is loaded so that our constant is defined
        require_once __DIR__ . '/DummyApps/ConstantDefineScalar/FooImplementation.php';
        $compiler = new InjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory(__DIR__ . '/DummyApps/ConstantDefineScalar', 'test');
        $injector = AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

        $subject = $injector->make(ConstantDefineScalar\FooImplementation::class);

        $this->assertSame('foo_bar_val', $subject->val);
    }

    public function testSimpleDefineScalarFromEnv() {
        $compiler = new InjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory(__DIR__ . '/DummyApps/SimpleDefineScalarFromEnv', 'test');
        $injector = AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

        $subject = $injector->make(SimpleDefineScalarFromEnv\FooImplementation::class);

        $this->assertSame(getenv('USER'), $subject->user);
    }

    public function testSimpleDefineServiceSetterInjection() {
        $compiler = new InjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory(__DIR__ . '/DummyApps/SimpleDefineService', 'test');
        $injector = AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

        $subject = $injector->make(SimpleDefineService\SetterInjection::class);

        $this->assertInstanceOf(SimpleDefineService\BazImplementation::class, $subject->baz);
        $this->assertInstanceOf(SimpleDefineService\BarImplementation::class, $subject->bar);
        $this->assertInstanceOf(SimpleDefineService\QuxImplementation::class, $subject->qux);
    }

    public function testSimpleDefineServiceConstructorInjection() {
        $compiler = new InjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory(__DIR__ . '/DummyApps/SimpleDefineService', 'test');
        $injector = AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

        $subject = $injector->make(SimpleDefineService\ConstructorInjection::class);

        $this->assertInstanceOf(SimpleDefineService\BazImplementation::class, $subject->baz);
        $this->assertInstanceOf(SimpleDefineService\BarImplementation::class, $subject->bar);
        $this->assertInstanceOf(SimpleDefineService\QuxImplementation::class, $subject->qux);
    }

    public function testMultipleAliasResolutionNoMakeDefine() {
        $compiler = new InjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory(__DIR__ . '/DummyApps/MultipleAliasResolution', 'test');
        $injector = AnnotatedInjectorFactory::fromInjectorDefinition($injectorDefinition);

        $this->expectException(InjectionException::class);
        $injector->make(MultipleAliasResolution\FooInterface::class);
    }

}