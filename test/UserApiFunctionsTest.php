<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Auryn\Injector;
use Cspray\AnnotatedContainer\Helper\StubAnnotatedContainerListener;
use Cspray\AnnotatedContainer\Internal\AfterContainerCreationAnnotatedContainerEvent;
use Cspray\AnnotatedContainer\Internal\BeforeContainerCreationAnnotatedContainerEvent;
use Cspray\AnnotatedContainerFixture\Fixtures;
use DI\Container;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;

class UserApiFunctionsTest extends TestCase {


    protected function setUp() : void {
        VirtualFilesystem::setup();
    }

    public function testCompilerFunctionNoCache() {
        $compiler = compiler();

        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build()
        );

        self::assertCount(1, $containerDefinition->getServiceDefinitions());
    }

    public function testCompilerFunctionWithCache() {
        $compiler = compiler('vfs://root');

        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build()
        );

        self::assertCount(1, $containerDefinition->getServiceDefinitions());
        self::assertFileExists('vfs://root/' . md5(Fixtures::singleConcreteService()->getPath()));
    }

    public function testContainerFactoryDefaultsToAuryn() {
        $backingContainer = containerFactory()->createContainer(
            ContainerDefinitionBuilder::newDefinition()->build()
        )->getBackingContainer();

        self::assertInstanceOf(Injector::class, $backingContainer);
    }

    public function testContainerFactoryRespectsPassingAurynExplicitly() {
        $backingContainer = containerFactory(SupportedContainers::Auryn)->createContainer(
            ContainerDefinitionBuilder::newDefinition()->build()
        )->getBackingContainer();

        self::assertInstanceOf(Injector::class, $backingContainer);
    }

    public function testContainerFactoryRespectsGettingNonDefault() {
        $backingContainer = containerFactory(SupportedContainers::PhpDi)->createContainer(
            ContainerDefinitionBuilder::newDefinition()->build()
        )->getBackingContainer();

        self::assertInstanceOf(Container::class, $backingContainer);
    }

    public function supportedContainerProvider() : array {
        return [
            [SupportedContainers::Default],
            [SupportedContainers::Auryn],
            [SupportedContainers::PhpDi]
        ];
    }

    /**
     * @dataProvider supportedContainerProvider
     */
    public function testContainerFactoryReturnsSameInstance(SupportedContainers $container) : void {
        $a = containerFactory($container);
        $b = containerFactory($container);

        self::assertSame($a, $b);
    }

}