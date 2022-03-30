<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\DummyApps\ServiceDelegate\ServiceInterface;
use Cspray\AnnotatedContainer\DummyApps\SimpleServices;
use Cspray\AnnotatedContainer\DummyApps\InterfaceServicePrepare;
use Cspray\AnnotatedContainer\DummyApps\SimpleUseScalar;
use Cspray\AnnotatedContainer\DummyApps\MultipleUseScalars;
use Cspray\AnnotatedContainer\DummyApps\ConstantUseScalar;
use Cspray\AnnotatedContainer\DummyApps\SimpleUseScalarFromEnv;
use Cspray\AnnotatedContainer\DummyApps\SimpleUseService;
use Cspray\AnnotatedContainer\DummyApps\MultipleAliasResolution;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;

/**
 * @covers \Cspray\AnnotatedContainer\AurynContainerFactory
 * @covers \Cspray\AnnotatedContainer\PhpParserContainerDefinitionCompiler
 * @covers \Cspray\AnnotatedContainer\ServiceDefinition
 * @covers \Cspray\AnnotatedContainer\AliasDefinition
 * @covers \Cspray\AnnotatedContainer\ServicePrepareDefinition
 * @covers \Cspray\AnnotatedContainer\InjectScalarDefinition
 */
class AurynContainerFactoryTest extends TestCase {

    public function testCreateSimpleServices() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleServices')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);
        $subject = $container->get(SimpleServices\FooInterface::class);

        $this->assertInstanceOf(SimpleServices\FooImplementation::class, $subject);
    }

    public function testInterfaceServicePrepare() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/InterfaceServicePrepare')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $subject = $container->get(InterfaceServicePrepare\FooInterface::class);

        $this->assertInstanceOf(InterfaceServicePrepare\FooImplementation::class, $subject);
        $this->assertEquals(1, $subject->getBarCounter());
    }

    public function testServicePrepareInvokedOnContainer() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/InjectorExecuteServicePrepare')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $subject = $container->get(DummyApps\InjectorExecuteServicePrepare\FooInterface::class);

        $this->assertInstanceOf(DummyApps\InjectorExecuteServicePrepare\FooImplementation::class, $subject);
        $this->assertInstanceOf(DummyApps\InjectorExecuteServicePrepare\BarImplementation::class, $subject->getBar());
    }

    public function testSimpleUseScalar() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleUseScalar')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $subject = $container->get(SimpleUseScalar\FooImplementation::class);

        $this->assertSame('string param test value', $subject->stringParam);
        $this->assertSame(42, $subject->intParam);
        $this->assertSame(42.0, $subject->floatParam);
        $this->assertTrue($subject->boolParam);
    }

    public function testMultipleUseScalars() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/MultipleUseScalars')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $subject = $container->get(MultipleUseScalars\FooImplementation::class);

        $this->assertSame('constructor param', $subject->stringParam);
        $this->assertSame('prepare param', $subject->prepareParam);
    }

    public function testConstantUseScalar() {
        // we need to make sure this file is loaded so that our constant is defined
        require_once DummyAppUtils::getRootDir() . '/ConstantUseScalar/FooImplementation.php';
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/ConstantUseScalar')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $subject = $container->get(ConstantUseScalar\FooImplementation::class);

        $this->assertSame('foo_bar_val', $subject->val);
    }

    public function testSimpleUseScalarFromEnv() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleUseScalarFromEnv')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $subject = $container->get(SimpleUseScalarFromEnv\FooImplementation::class);

        $this->assertSame(getenv('USER'), $subject->user);
    }

    public function testSimpleUseServiceSetterInjection() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleUseService')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $subject = $container->get(SimpleUseService\SetterInjection::class);

        $this->assertInstanceOf(SimpleUseService\BazImplementation::class, $subject->baz);
        $this->assertInstanceOf(SimpleUseService\BarImplementation::class, $subject->bar);
        $this->assertInstanceOf(SimpleUseService\QuxImplementation::class, $subject->qux);
    }

    public function testSimpleUseServiceConstructorInjection() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleUseService')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $subject = $container->get(SimpleUseService\ConstructorInjection::class);

        $this->assertInstanceOf(SimpleUseService\BazImplementation::class, $subject->baz);
        $this->assertInstanceOf(SimpleUseService\BarImplementation::class, $subject->bar);
        $this->assertInstanceOf(SimpleUseService\QuxImplementation::class, $subject->qux);
    }

    public function testMultipleAliasResolutionNoMakeDefine() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/MultipleAliasResolution')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $this->expectException(ContainerExceptionInterface::class);
        $container->get(MultipleAliasResolution\FooInterface::class);
    }

    public function testServiceDelegate() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/ServiceDelegate')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $service = $container->get(ServiceInterface::class);

        $this->assertSame('From ServiceFactory From FooService', $service->getValue());
    }

    public function testHasServiceIfCompiled() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleServices')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $this->assertTrue($container->has(DummyApps\SimpleServices\FooInterface::class));
        $this->assertFalse($container->has(DummyApps\MultipleSimpleServices\FooInterface::class));
    }

    public function testMultipleServicesWithPrimary() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/MultipleServicesWithPrimary')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $this->assertInstanceOf(DummyApps\MultipleServicesWithPrimary\FooImplementation::class, $container->get(DummyApps\MultipleServicesWithPrimary\FooInterface::class));
    }

    public function testProfileResolvedServices() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/ProfileResolvedServices')->build()
        );
        $container = (new AurynContainerFactory())->createContainer(
            $containerDefinition,
            new class implements ContainerFactoryOptions {
                public function getActiveProfiles(): array {
                    return ['default', 'dev'];
                }
            }
        );

        $instance = $container->get(DummyApps\ProfileResolvedServices\FooInterface::class);

        $this->assertNotNull($instance);
        $this->assertInstanceOf(DummyApps\ProfileResolvedServices\DevFooImplementation::class, $instance);
    }

    public function testInjectScalarProfilesDev() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/InjectScalarProfiles')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition, new class implements ContainerFactoryOptions {

            public function getActiveProfiles(): array {
                return ['default', 'dev'];
            }
        });

        $instance = $container->get(DummyApps\InjectScalarProfiles\FooImplementation::class);

        $this->assertNotNull($instance);
        $this->assertSame('foo', $instance->getValue());
    }

    public function testInjectScalarProfilesProd() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/InjectScalarProfiles')->build()
        );
        $container = (new AurynContainerFactory())->createContainer(
            $containerDefinition,
            new class implements ContainerFactoryOptions {

                public function getActiveProfiles(): array {
                    return ['default', 'prod'];
                }
            }
        );

        $instance = $container->get(DummyApps\InjectScalarProfiles\FooImplementation::class);

        $this->assertNotNull($instance);
        $this->assertSame('bar', $instance->getValue());
    }

    public function testInjectScalarProfilesTest() {
        $compiler = new PhpParserContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/InjectScalarProfiles')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition, new class implements ContainerFactoryOptions {

            public function getActiveProfiles(): array {
                return ['default', 'test'];
            }
        });

        $instance = $container->get(DummyApps\InjectScalarProfiles\FooImplementation::class);

        $this->assertNotNull($instance);
        $this->assertSame('baz', $instance->getValue());
    }

}