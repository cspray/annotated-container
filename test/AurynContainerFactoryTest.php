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
 * @covers \Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompiler
 * @covers \Cspray\AnnotatedContainer\AliasDefinitionBuilder
 * @covers \Cspray\AnnotatedContainer\Attribute\Service
 * @covers \Cspray\AnnotatedContainer\ContainerDefinitionBuilder
 * @covers \Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder
 * @covers \Cspray\AnnotatedContainer\DefaultAnnotatedTargetDefinitionConverter
 * @covers \Cspray\AnnotatedContainer\PhpParserAnnotatedTargetCompiler
 * @covers \Cspray\AnnotatedContainer\ServiceDefinitionBuilder
 * @covers \Cspray\AnnotatedContainer\ContainerFactoryOptionsBuilder
 * @covers \Cspray\AnnotatedContainer\ServicePrepareDefinitionBuilder
 * @covers \Cspray\AnnotatedContainer\Attribute\ServiceDelegate
 * @covers \Cspray\AnnotatedContainer\ServiceDelegateDefinitionBuilder
 */
class AurynContainerFactoryTest extends TestCase {

    private function getContainerDefinitionCompiler() : ContainerDefinitionCompiler {
        return new AnnotatedTargetContainerDefinitionCompiler(
            new PhpParserAnnotatedTargetCompiler(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
    }

    public function testCreateSimpleServices() {
        $compiler = $this->getContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleServices')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);
        $subject = $container->get(SimpleServices\FooInterface::class);

        $this->assertInstanceOf(SimpleServices\FooImplementation::class, $subject);
    }

    public function testInterfaceServicePrepare() {
        $compiler = $this->getContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/InterfaceServicePrepare')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $subject = $container->get(InterfaceServicePrepare\FooInterface::class);

        $this->assertInstanceOf(InterfaceServicePrepare\FooImplementation::class, $subject);
        $this->assertEquals(1, $subject->getBarCounter());
    }

    public function testServicePrepareInvokedOnContainer() {
        $compiler = $this->getContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/InjectorExecuteServicePrepare')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $subject = $container->get(DummyApps\InjectorExecuteServicePrepare\FooInterface::class);

        $this->assertInstanceOf(DummyApps\InjectorExecuteServicePrepare\FooImplementation::class, $subject);
        $this->assertInstanceOf(DummyApps\InjectorExecuteServicePrepare\BarImplementation::class, $subject->getBar());
    }

    public function testMultipleAliasResolutionNoMakeDefine() {
        $compiler = $this->getContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/MultipleAliasResolution')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $this->expectException(ContainerExceptionInterface::class);
        $container->get(MultipleAliasResolution\FooInterface::class);
    }

    public function testServiceDelegate() {
        $compiler = $this->getContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/ServiceDelegate')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $service = $container->get(ServiceInterface::class);

        $this->assertSame('From ServiceFactory From FooService', $service->getValue());
    }

    public function testHasServiceIfCompiled() {
        $compiler = $this->getContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleServices')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $this->assertTrue($container->has(DummyApps\SimpleServices\FooInterface::class));
        $this->assertFalse($container->has(DummyApps\MultipleSimpleServices\FooInterface::class));
    }

    public function testMultipleServicesWithPrimary() {
        $compiler = $this->getContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/MultipleServicesWithPrimary')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition);

        $this->assertInstanceOf(DummyApps\MultipleServicesWithPrimary\FooImplementation::class, $container->get(DummyApps\MultipleServicesWithPrimary\FooInterface::class));
    }

    public function testProfileResolvedServices() {
        $compiler = $this->getContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/ProfileResolvedServices')->build()
        );
        $container = (new AurynContainerFactory())->createContainer(
            $containerDefinition,
            ContainerFactoryOptionsBuilder::forActiveProfiles('default', 'dev')->build()
        );

        $instance = $container->get(DummyApps\ProfileResolvedServices\FooInterface::class);

        $this->assertNotNull($instance);
        $this->assertInstanceOf(DummyApps\ProfileResolvedServices\DevFooImplementation::class, $instance);
    }

    public function testCreateNamedService() {
        $compiler = $this->getContainerDefinitionCompiler();
        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/NamedService')->build()
        );
        $container = (new AurynContainerFactory())->createContainer($containerDefinition, ContainerFactoryOptionsBuilder::forActiveProfiles('default')->build());

        $this->assertTrue($container->has('foo'));

        $instance = $container->get('foo');

        $this->assertNotNull($instance);
        $this->assertInstanceOf(DummyApps\NamedService\FooImplementation::class, $instance);
    }

}