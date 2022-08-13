<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Helper\FixtureBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Helper\StubContextConsumerWithDependencies;
use Cspray\AnnotatedContainer\Helper\StubParameterStoreWithDependencies;
use Cspray\AnnotatedContainer\Helper\TestLogger;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use Psr\Log\LogLevel;

final class BootstrapTest extends TestCase {

    private VirtualDirectory $vfs;

    protected function setUp() : void {
        parent::setUp();
        $this->vfs = VirtualFilesystem::setup();
    }

    public function testBootstrapSingleConcreteServiceNoCache() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);
        $container = $bootstrap->bootstrapContainer();

        $service = $container->get(Fixtures::singleConcreteService()->fooImplementation()->getName());

        self::assertInstanceOf(
            Fixtures::singleConcreteService()->fooImplementation()->getName(),
            $service
        );
    }

    public function testBootstrapSingleConcreteServiceWithCache() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
    <cacheDir>.annotated-container-cache</cacheDir>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        VirtualFilesystem::newDirectory('.annotated-container-cache')
            ->at($this->vfs);

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);
        $bootstrap->bootstrapContainer();
        $expected = md5(Fixtures::singleConcreteService()->getPath());

        self::assertFileExists('vfs://root/.annotated-container-cache/' . $expected);
    }

    public function testBootstrapWithValidContextConsumers() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>ThirdPartyServices</dir>
        </source>
    </scanDirectories>
    <containerDefinitionBuilderContextConsumer>
      Cspray\AnnotatedContainer\Helper\StubContextConsumer
    </containerDefinitionBuilderContextConsumer>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);
        $container = $bootstrap->bootstrapContainer();

        $service = $container->get(Fixtures::thirdPartyServices()->fooInterface()->getName());
        self::assertInstanceOf(Fixtures::thirdPartyServices()->fooImplementation()->getName(), $service);
    }

    public function testBootstrapWithParameterStores() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>InjectCustomStoreServices</dir>
    </source>
  </scanDirectories>
  <parameterStores>
    <fqcn>Cspray\AnnotatedContainer\Helper\StubParameterStore</fqcn>
  </parameterStores>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);
        $container = $bootstrap->bootstrapContainer();

        $service = $container->get(Fixtures::injectCustomStoreServices()->scalarInjector()->getName());
        self::assertInstanceOf(Fixtures::injectCustomStoreServices()->scalarInjector()->getName(), $service);
        self::assertSame('from test-store key', $service->key);
    }

    public function testBootstrapResolvesProfileServices() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>ProfileResolvedServices</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);
        $container = $bootstrap->bootstrapContainer(profiles: ['default', 'dev']);
        $service = $container->get(Fixtures::profileResolvedServices()->fooInterface()->getName());
        self::assertInstanceOf(Fixtures::profileResolvedServices()->devImplementation()->getName(), $service);
    }

    public function testBootstrapSingleConcreteServiceUsesCustomFileName() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('my-container.xml.dist')
            ->withContent($goodXml)
            ->at($this->vfs);

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);
        $container = $bootstrap->bootstrapContainer(configurationFile: 'my-container.xml.dist');

        $service = $container->get(Fixtures::singleConcreteService()->fooImplementation()->getName());

        self::assertInstanceOf(
            Fixtures::singleConcreteService()->fooImplementation()->getName(),
            $service
        );
    }

    public function testBootstrapWithLogging() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
    <logging>
      <file>annotated-container.log</file>
    </logging>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        $container = (new Bootstrap(directoryResolver: $directoryResolver))->bootstrapContainer();

        self::assertFileExists('vfs://root/annotated-container.log');
        $logContents = file_get_contents('vfs://root/annotated-container.log');
        self::assertStringContainsString('Annotated Container compiling started.', $logContents);
        self::assertStringContainsString(
            sprintf(
                'Started wiring AnnotatedContainer with %s backing implementation and "default" active profiles.',
                $container->getBackingContainer()::class,
            ),
            $logContents
        );
    }

    public function testBootstrapWithLoggingProfileExcluded() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
    <logging>
      <file>annotated-container.log</file>
      <exclude><profile>test</profile></exclude>
    </logging>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        (new Bootstrap(directoryResolver: $directoryResolver))->bootstrapContainer(['default', 'test']);

        self::assertFileExists('vfs://root/annotated-container.log');
        $logContents = file_get_contents('vfs://root/annotated-container.log');
        self::assertSame('', $logContents);
    }

    public function testBootstrapWithLoggerPassedOverridesConfiguration() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
    <logging>
      <file>annotated-container.log</file>
    </logging>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        $logger = new TestLogger();
        (new Bootstrap(directoryResolver: $directoryResolver, logger: $logger))->bootstrapContainer();

        self::assertStringEqualsFile('vfs://root/annotated-container.log', '');
        self::assertGreaterThan(1, count($logger->getLogsForLevel(LogLevel::INFO)));
    }

    public function testBoostrapContextConsumerFactoryPassedToConfiguration() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>ThirdPartyServices</dir>
        </source>
    </scanDirectories>
    <containerDefinitionBuilderContextConsumer>
      Cspray\AnnotatedContainer\Helper\StubContextConsumerWithDependencies
    </containerDefinitionBuilderContextConsumer>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        $factory = new class implements ContainerDefinitionBuilderContextConsumerFactory {

            public function createConsumer(string $identifier) : ContainerDefinitionBuilderContextConsumer {
                if ($identifier === StubContextConsumerWithDependencies::class) {
                    return new StubContextConsumerWithDependencies(Fixtures::thirdPartyServices()->fooImplementation());
                } else {
                    throw new \RuntimeException();
                }
            }
        };

        $container = (new Bootstrap(directoryResolver: $directoryResolver, containerDefinitionBuilderContextConsumerFactory: $factory))->bootstrapContainer();

        $service = $container->get(Fixtures::thirdPartyServices()->fooInterface()->getName());

        self::assertInstanceOf(Fixtures::thirdPartyServices()->fooImplementation()->getName(), $service);
    }

    public function testBootstrapParameterStoreFactoryPassedToConfiguration() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>InjectCustomStoreServices</dir>
        </source>
    </scanDirectories>
    <parameterStores>
      <fqcn>Cspray\AnnotatedContainer\Helper\StubParameterStoreWithDependencies</fqcn>
    </parameterStores>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        $factory = new class implements ParameterStoreFactory {

            public function createParameterStore(string $identifier) : ParameterStore {
                if ($identifier === StubParameterStoreWithDependencies::class) {
                    return new StubParameterStoreWithDependencies('ac-ac');
                } else {
                    throw new \RuntimeException();
                }
            }
        };

        $container = (new Bootstrap(directoryResolver: $directoryResolver, parameterStoreFactory: $factory))->bootstrapContainer();

        $service = $container->get(Fixtures::injectCustomStoreServices()->scalarInjector()->getName());

        self::assertSame('ac-ackey', $service->key);
    }

}