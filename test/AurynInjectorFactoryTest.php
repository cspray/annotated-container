<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Auryn\InjectionException;
use Cspray\AnnotatedContainer\DummyApps\ServiceDelegate\ServiceInterface;
use Cspray\AnnotatedContainer\DummyApps\SimpleServices;
use Cspray\AnnotatedContainer\DummyApps\InterfaceServicePrepare;
use Cspray\AnnotatedContainer\DummyApps\InjectorExecuteServicePrepare;
use Cspray\AnnotatedContainer\DummyApps\SimpleUseScalar;
use Cspray\AnnotatedContainer\DummyApps\MultipleUseScalars;
use Cspray\AnnotatedContainer\DummyApps\ConstantUseScalar;
use Cspray\AnnotatedContainer\DummyApps\SimpleUseScalarFromEnv;
use Cspray\AnnotatedContainer\DummyApps\SimpleUseService;
use Cspray\AnnotatedContainer\DummyApps\MultipleAliasResolution;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\AnnotatedContainer\AurynInjectorFactory
 * @covers \Cspray\AnnotatedContainer\PhpParserContainerDefinitionCompiler
 * @covers \Cspray\AnnotatedContainer\Internal\Visitor\ServiceDefinitionVisitor
 * @covers \Cspray\AnnotatedContainer\Internal\Visitor\ServicePrepareDefinitionVisitor
 * @covers \Cspray\AnnotatedContainer\Internal\Visitor\InjectScalarDefinitionVisitor
 * @covers \Cspray\AnnotatedContainer\Internal\Interrogator\ServiceDefinitionInterrogator
 * @covers \Cspray\AnnotatedContainer\Internal\Interrogator\ServicePrepareDefinitionInterrogator
 * @covers \Cspray\AnnotatedContainer\Internal\Interrogator\InjectScalarDefinitionInterrogator
 * @covers \Cspray\AnnotatedContainer\ServiceDefinition
 * @covers \Cspray\AnnotatedContainer\AliasDefinition
 * @covers \Cspray\AnnotatedContainer\ServicePrepareDefinition
 * @covers \Cspray\AnnotatedContainer\InjectScalarDefinition
 * @covers \Cspray\AnnotatedContainer\Internal\Visitor\AbstractNodeVisitor
 */
class AurynInjectorFactoryTest extends TestCase {

    public function testCreateSimpleServices() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $injectorDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories( __DIR__ . '/DummyApps/SimpleServices')
                ->withProfiles('default')
                ->build()
        );
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);
        $subject = $injector->get(SimpleServices\FooInterface::class);

        $this->assertInstanceOf(SimpleServices\FooImplementation::class, $subject);
    }

    public function testInterfaceServicePrepare() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $injectorDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/InterfaceServicePrepare')
                ->withProfiles('default')
                ->build()
        );
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->get(InterfaceServicePrepare\FooInterface::class);

        $this->assertInstanceOf(InterfaceServicePrepare\FooImplementation::class, $subject);
        $this->assertEquals(1, $subject->getBarCounter());
    }

    public function testServicePrepareInvokedOnInjector() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $injectorDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/InjectorExecuteServicePrepare')
                ->withProfiles('default')
                ->build()
        );
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->get(InjectorExecuteServicePrepare\FooInterface::class);

        $this->assertInstanceOf(InjectorExecuteServicePrepare\FooImplementation::class, $subject);
        $this->assertInstanceOf(InjectorExecuteServicePrepare\BarImplementation::class, $subject->getBar());
    }

    public function testSimpleUseScalar() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $injectorDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseScalar')
                ->withProfiles('default')
                ->build()
        );
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->get(SimpleUseScalar\FooImplementation::class);

        $this->assertSame('string param test value', $subject->stringParam);
        $this->assertSame(42, $subject->intParam);
        $this->assertSame(42.0, $subject->floatParam);
        $this->assertTrue($subject->boolParam);
    }

    public function testMultipleUseScalars() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $injectorDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/MultipleUseScalars')
                ->withProfiles('default')
                ->build()
        );
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->get(MultipleUseScalars\FooImplementation::class);

        $this->assertSame('constructor param', $subject->stringParam);
        $this->assertSame('prepare param', $subject->prepareParam);
    }

    public function testConstantUseScalar() {
        // we need to make sure this file is loaded so that our constant is defined
        require_once __DIR__ . '/DummyApps/ConstantUseScalar/FooImplementation.php';
        $compiler = new PhpParserContainerDefinitionCompiler();
        $injectorDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/ConstantUseScalar')
                ->withProfiles('default')
                ->build()
        );
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->get(ConstantUseScalar\FooImplementation::class);

        $this->assertSame('foo_bar_val', $subject->val);
    }

    public function testSimpleUseScalarFromEnv() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $injectorDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseScalarFromEnv')
                ->withProfiles('default')
                ->build()
        );
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->get(SimpleUseScalarFromEnv\FooImplementation::class);

        $this->assertSame(getenv('USER'), $subject->user);
    }

    public function testSimpleUseServiceSetterInjection() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $injectorDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseService')
                ->withProfiles('default')
                ->build()
        );
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->get(SimpleUseService\SetterInjection::class);

        $this->assertInstanceOf(SimpleUseService\BazImplementation::class, $subject->baz);
        $this->assertInstanceOf(SimpleUseService\BarImplementation::class, $subject->bar);
        $this->assertInstanceOf(SimpleUseService\QuxImplementation::class, $subject->qux);
    }

    public function testSimpleUseServiceConstructorInjection() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $injectorDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/SimpleUseService')
                ->withProfiles('default')
                ->build()
        );
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $subject = $injector->get(SimpleUseService\ConstructorInjection::class);

        $this->assertInstanceOf(SimpleUseService\BazImplementation::class, $subject->baz);
        $this->assertInstanceOf(SimpleUseService\BarImplementation::class, $subject->bar);
        $this->assertInstanceOf(SimpleUseService\QuxImplementation::class, $subject->qux);
    }

    public function testMultipleAliasResolutionNoMakeDefine() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $injectorDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/MultipleAliasResolution')
                ->withProfiles('default')
                ->build()
        );
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $this->expectException(InjectionException::class);
        $injector->get(MultipleAliasResolution\FooInterface::class);
    }

    public function testServiceDelegate() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $injectorDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(__DIR__ . '/DummyApps/ServiceDelegate')
                ->withProfiles('default')
                ->build()
        );
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $service = $injector->get(ServiceInterface::class);

        $this->assertSame('From ServiceFactory From FooService', $service->getValue());
    }

    public function testHasServiceIfCompiled() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $injectorDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories( __DIR__ . '/DummyApps/SimpleServices')
                ->withProfiles('default')
                ->build()
        );
        $injector = (new AurynInjectorFactory())->createContainer($injectorDefinition);

        $this->assertTrue($injector->has(DummyApps\SimpleServices\FooInterface::class));
        $this->assertFalse($injector->has(DummyApps\MultipleSimpleServices\FooInterface::class));
    }

}