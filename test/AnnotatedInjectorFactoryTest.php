<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

use Auryn\InjectionException;
use Cspray\AnnotatedInjector\DummyApps\ServiceDelegate\ServiceInterface;
use Cspray\AnnotatedInjector\DummyApps\SimpleServices;
use Cspray\AnnotatedInjector\DummyApps\InterfaceServicePrepare;
use Cspray\AnnotatedInjector\DummyApps\InjectorExecuteServicePrepare;
use Cspray\AnnotatedInjector\DummyApps\SimpleUseScalar;
use Cspray\AnnotatedInjector\DummyApps\MultipleUseScalars;
use Cspray\AnnotatedInjector\DummyApps\ConstantUseScalar;
use Cspray\AnnotatedInjector\DummyApps\SimpleUseScalarFromEnv;
use Cspray\AnnotatedInjector\DummyApps\SimpleUseService;
use Cspray\AnnotatedInjector\DummyApps\MultipleAliasResolution;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\AnnotatedInjector\AurynInjectorFactory
 * @covers \Cspray\AnnotatedInjector\PhpParserInjectorDefinitionCompiler
 * @covers \Cspray\AnnotatedInjector\Internal\Visitor\ServiceDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Internal\Visitor\ServicePrepareDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Internal\Visitor\UseScalarDefinitionVisitor
 * @covers \Cspray\AnnotatedInjector\Internal\Interrogator\ServiceDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\Internal\Interrogator\ServicePrepareDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\Internal\Interrogator\UseScalarDefinitionInterrogator
 * @covers \Cspray\AnnotatedInjector\ServiceDefinition
 * @covers \Cspray\AnnotatedInjector\AliasDefinition
 * @covers \Cspray\AnnotatedInjector\ServicePrepareDefinition
 * @covers \Cspray\AnnotatedInjector\UseScalarDefinition
 * @covers \Cspray\AnnotatedInjector\Internal\Visitor\AbstractNodeVisitor
 */
class AnnotatedInjectorFactoryTest extends TestCase {

    public function testCreateSimpleServices() {
        $compiler = new PhpParserInjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory('test', __DIR__ . '/DummyApps/SimpleServices');
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);
        $subject = $injector->make(SimpleServices\FooInterface::class);

        $this->assertInstanceOf(SimpleServices\FooImplementation::class, $subject);
    }

    public function testInterfaceServicePrepare() {
        $compiler = new PhpParserInjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory('test', __DIR__ . '/DummyApps/InterfaceServicePrepare');
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->make(InterfaceServicePrepare\FooInterface::class);

        $this->assertInstanceOf(InterfaceServicePrepare\FooImplementation::class, $subject);
        $this->assertEquals(1, $subject->getBarCounter());
    }

    public function testServicePrepareInvokedOnInjector() {
        $compiler = new PhpParserInjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory('test', __DIR__ . '/DummyApps/InjectorExecuteServicePrepare');
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->make(InjectorExecuteServicePrepare\FooInterface::class);

        $this->assertInstanceOf(InjectorExecuteServicePrepare\FooImplementation::class, $subject);
        $this->assertInstanceOf(InjectorExecuteServicePrepare\BarImplementation::class, $subject->getBar());
    }

    public function testSimpleUseScalar() {
        $compiler = new PhpParserInjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory('test', __DIR__ . '/DummyApps/SimpleUseScalar');
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->make(SimpleUseScalar\FooImplementation::class);

        $this->assertSame('string param test value', $subject->stringParam);
        $this->assertSame(42, $subject->intParam);
        $this->assertSame(42.0, $subject->floatParam);
        $this->assertTrue($subject->boolParam);
    }

    public function testMultipleUseScalars() {
        $compiler = new PhpParserInjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory('test', __DIR__ . '/DummyApps/MultipleUseScalars');
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->make(MultipleUseScalars\FooImplementation::class);

        $this->assertSame('constructor param', $subject->stringParam);
        $this->assertSame('prepare param', $subject->prepareParam);
    }

    public function testConstantUseScalar() {
        // we need to make sure this file is loaded so that our constant is defined
        require_once __DIR__ . '/DummyApps/ConstantUseScalar/FooImplementation.php';
        $compiler = new PhpParserInjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory('test', __DIR__ . '/DummyApps/ConstantUseScalar');
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->make(ConstantUseScalar\FooImplementation::class);

        $this->assertSame('foo_bar_val', $subject->val);
    }

    public function testSimpleUseScalarFromEnv() {
        $compiler = new PhpParserInjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory('test', __DIR__ . '/DummyApps/SimpleUseScalarFromEnv');
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->make(SimpleUseScalarFromEnv\FooImplementation::class);

        $this->assertSame(getenv('USER'), $subject->user);
    }

    public function testSimpleUseServiceSetterInjection() {
        $compiler = new PhpParserInjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory('test', __DIR__ . '/DummyApps/SimpleUseService');
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->make(SimpleUseService\SetterInjection::class);

        $this->assertInstanceOf(SimpleUseService\BazImplementation::class, $subject->baz);
        $this->assertInstanceOf(SimpleUseService\BarImplementation::class, $subject->bar);
        $this->assertInstanceOf(SimpleUseService\QuxImplementation::class, $subject->qux);
    }

    public function testSimpleUseServiceConstructorInjection() {
        $compiler = new PhpParserInjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory('test', __DIR__ . '/DummyApps/SimpleUseService');
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->make(SimpleUseService\ConstructorInjection::class);

        $this->assertInstanceOf(SimpleUseService\BazImplementation::class, $subject->baz);
        $this->assertInstanceOf(SimpleUseService\BarImplementation::class, $subject->bar);
        $this->assertInstanceOf(SimpleUseService\QuxImplementation::class, $subject->qux);
    }

    public function testMultipleAliasResolutionNoMakeDefine() {
        $compiler = new PhpParserInjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory('test', __DIR__ . '/DummyApps/MultipleAliasResolution');
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $this->expectException(InjectionException::class);
        $injector->make(MultipleAliasResolution\FooInterface::class);
    }

    public function testServiceDelegate() {
        $compiler = new PhpParserInjectorDefinitionCompiler();
        $injectorDefinition = $compiler->compileDirectory('test', __DIR__ . '/DummyApps/ServiceDelegate');
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $service = $injector->make(ServiceInterface::class);

        $this->assertSame('From ServiceFactory From FooService', $service->getValue());
    }

}