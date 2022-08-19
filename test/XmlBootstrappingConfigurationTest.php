<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Bootstrap\Observer;
use Cspray\AnnotatedContainer\Bootstrap\ObserverFactory;
use Cspray\AnnotatedContainer\Exception\InvalidBootstrapConfiguration;
use Cspray\AnnotatedContainer\Helper\AdditionalStubContextConsumer;
use Cspray\AnnotatedContainer\Helper\FixtureBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Helper\StubBootstrapObserverWithDependencies;
use Cspray\AnnotatedContainer\Helper\StubContextConsumer;
use Cspray\AnnotatedContainer\Helper\StubContextConsumerWithDependencies;
use Cspray\AnnotatedContainer\Helper\StubParameterStore;
use Cspray\AnnotatedContainer\Internal\CompositeLogger;
use Cspray\AnnotatedContainer\Internal\FileLogger;
use Cspray\AnnotatedContainer\Internal\StdoutLogger;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;

use function Cspray\Typiphy\objectType;
use function Cspray\Typiphy\stringType;

class XmlBootstrappingConfigurationTest extends TestCase {

    private VirtualDirectory $vfs;

    protected function setUp() : void {
        parent::setUp();
        $this->vfs = VirtualFilesystem::setup();
    }

    public function testXmlDoesNotValidateSchemaThrowsError() : void {
        $badXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer />
XML;
        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($badXml)
            ->at($this->vfs);
        self::expectException(InvalidBootstrapConfiguration::class);
        self::expectExceptionMessage(
            'Configuration file vfs://root/annotated-container.xml does not validate against the appropriate schema.'
        );
        new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );
    }

    public function testValidXmlReturnsScanDirectories() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
            <dir>test/helper</dir>
            <dir>lib</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;
        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);
        $configuration = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );

        self::assertSame(
            ['src', 'test/helper', 'lib'],
            $configuration->getScanDirectories()
        );
    }

    public function testValidXmlReturnsContextConsumers() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
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

        $configuration = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );
        self::assertInstanceOf(
            StubContextConsumer::class,
            $configuration->getContainerDefinitionConsumer()
        );
    }

    public function testContainerContextConsumersNotClass() : void {
        $badXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
    <containerDefinitionBuilderContextConsumer>
        FooBar
    </containerDefinitionBuilderContextConsumer>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($badXml)
            ->at($this->vfs);

        self::expectException(InvalidBootstrapConfiguration::class);
        self::expectExceptionMessage(
            'All entries in containerDefinitionBuilderContextConsumers must be classes that implement ' . ContainerDefinitionBuilderContextConsumer::class
        );

        new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );
    }

    public function testContainerContextConsumersNotImplementCorrectInterface() : void {
        $badXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
    <containerDefinitionBuilderContextConsumer>
        Cspray\AnnotatedContainer\XmlBootstrappingConfiguration
    </containerDefinitionBuilderContextConsumer>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($badXml)
            ->at($this->vfs);

        self::expectException(InvalidBootstrapConfiguration::class);
        self::expectExceptionMessage(
            'All entries in containerDefinitionBuilderContextConsumers must be classes that implement ' . ContainerDefinitionBuilderContextConsumer::class
        );

        new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );
    }

    public function testContainerContextConsumersEmptyIfNoneDefined() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );

        self::assertNull($config->getContainerDefinitionConsumer());
    }

    public function testCacheDirNotSpecifiedReturnsNull() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );
        self::assertNull($config->getCacheDirectory());
    }

    public function testCacheDirSpecifiedIsReturned() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
    <cacheDir>cache</cacheDir>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );
        self::assertSame('cache', $config->getCacheDirectory());
    }

    public function testParameterStoresReturned() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
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

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );

        self::assertCount(1, $config->getParameterStores());
        self::assertContainsOnlyInstancesOf(StubParameterStore::class, $config->getParameterStores());
    }

    public function testParameterStoreContainsNonClassThrowsException() : void {
        $badXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <parameterStores>
    <fqcn>something not a class</fqcn>
  </parameterStores>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($badXml)
            ->at($this->vfs);

        self::expectException(InvalidBootstrapConfiguration::class);
        self::expectExceptionMessage(
            'All entries in parameterStores must be classes that implement ' . ParameterStore::class
        );
        new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );
    }

    public function testParameterStoreContainsNotParameterStoreThrowsException() : void {
        $badXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <parameterStores>
    <fqcn>Cspray\AnnotatedContainer\Helper\StubContextConsumer</fqcn>
  </parameterStores>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($badXml)
            ->at($this->vfs);

        self::expectException(InvalidBootstrapConfiguration::class);
        self::expectExceptionMessage(
            'All entries in parameterStores must be classes that implement ' . ParameterStore::class
        );
        new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );
    }

    public function testParameterStoreFactoryPresentRespected() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <parameterStores>
    <fqcn>Cspray\AnnotatedContainer\Helper\StubParameterStoreWithDependencies</fqcn>
  </parameterStores>
</annotatedContainer>
XML;

        $parameterStoreFactory = new class implements ParameterStoreFactory {

            /**
             * @param class-string<ParameterStore> $identifier
             * @return ParameterStore
             */
            public function createParameterStore(string $identifier) : ParameterStore {
                return new $identifier('passed to constructor ');
            }
        };

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver(),
            $parameterStoreFactory
        );

        self::assertCount(1, $config->getParameterStores());
        self::assertSame('passed to constructor my-key', $config->getParameterStores()[0]->fetch(stringType(), 'my-key'));
    }

    public function testContainerDefinitionBuilderContextConsumerFactoryPresentRespected() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <containerDefinitionBuilderContextConsumer>
    Cspray\AnnotatedContainer\Helper\StubContextConsumerWithDependencies
  </containerDefinitionBuilderContextConsumer>
</annotatedContainer>
XML;

        $consumerFactory = new class implements ContainerDefinitionBuilderContextConsumerFactory {
            public function createConsumer(string $identifier) : ContainerDefinitionBuilderContextConsumer {
                return new $identifier(Fixtures::thirdPartyServices()->fooInterface());
            }
        };

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver(),
            consumerFactory: $consumerFactory
        );

        self::assertInstanceOf(StubContextConsumerWithDependencies::class, $config->getContainerDefinitionConsumer());
    }

    public function testLoggingFileConfigurationReturnsCorrectLogger() : void {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <logging>
    <file>logs/annotated-container.log</file>
  </logging>
</annotatedContainer>
XML;

        VirtualFilesystem::newDirectory('logs')->at($this->vfs);
        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        self::assertFileDoesNotExist('vfs://root/logs/annotated-container.log');

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );

        self::assertInstanceOf(FileLogger::class, $config->getLogger());
        self::assertFileExists('vfs://root/logs/annotated-container.log');
    }

    public function testLoggingFileConfigurationReturnsCorrectStdoutLogger() : void {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <logging>
    <stdout />
  </logging>
</annotatedContainer>
XML;

        VirtualFilesystem::newDirectory('logs')->at($this->vfs);
        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        self::assertFileDoesNotExist('vfs://root/logs/annotated-container.log');

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );

        self::assertInstanceOf(StdoutLogger::class, $config->getLogger());
        self::assertFileDoesNotExist('vfs://root/logs/annotated-container.log');
    }

    public function testLoggingFileAndStdoutConfigurationReturnsCorrectLogger() : void {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <logging>
    <file>logs/annotated-container.log</file>
    <stdout />
  </logging>
</annotatedContainer>
XML;

        VirtualFilesystem::newDirectory('logs')->at($this->vfs);
        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        self::assertFileDoesNotExist('vfs://root/logs/annotated-container.log');

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );
        $logger = $config->getLogger();

        self::assertInstanceOf(CompositeLogger::class, $logger);
        self::assertCount(2, $logger->getLoggers());

        self::assertInstanceOf(FileLogger::class, $logger->getLoggers()[0]);
        self::assertInstanceOf(StdoutLogger::class, $logger->getLoggers()[1]);
    }

    public function testLoggingFileAndStdoutConfigurationReturnsCorrectExcludedLoggingProfiles() : void {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <logging>
    <file>logs/annotated-container.log</file>
    <stdout />
    <exclude>
      <profile>foo</profile>
      <profile>bar</profile>
      <profile>baz</profile>
    </exclude>
  </logging>
</annotatedContainer>
XML;

        VirtualFilesystem::newDirectory('logs')->at($this->vfs);
        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        self::assertFileDoesNotExist('vfs://root/logs/annotated-container.log');

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );

        self::assertSame(['foo', 'bar', 'baz'], $config->getLoggingExcludedProfiles());
    }

    public function testObserversContainsNonClassThrowsException() : void {
        $badXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <observers>
    <fqcn>something not a class</fqcn>
  </observers>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($badXml)
            ->at($this->vfs);

        self::expectException(InvalidBootstrapConfiguration::class);
        self::expectExceptionMessage(
            'All entries in observers must be classes that implement ' . Observer::class
        );
        new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );
    }

    public function testObserversContainsNotObserverThrowsException() : void {
        $badXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <observers>
    <fqcn>Cspray\AnnotatedContainer\Helper\StubContextConsumer</fqcn>
  </observers>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($badXml)
            ->at($this->vfs);

        self::expectException(InvalidBootstrapConfiguration::class);
        self::expectExceptionMessage(
            'All entries in observers must be classes that implement ' . Observer::class
        );
        new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );
    }

    public function testObserverFactoryPresentRespected() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <observers><fqcn>Cspray\AnnotatedContainer\Helper\StubBootstrapObserverWithDependencies</fqcn></observers>
</annotatedContainer>
XML;

        $observerFactory = new class implements ObserverFactory {
            public function createObserver(string $observer) : Observer {
                return new $observer('from observer factory');
            }
        };

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver(),
            observerFactory: $observerFactory
        );

        self::assertCount(1, $config->getObservers());
        self::assertInstanceOf(StubBootstrapObserverWithDependencies::class, $config->getObservers()[0]);
    }
}
