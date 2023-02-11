<?php

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Bootstrap\DefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\Observer;
use Cspray\AnnotatedContainer\Bootstrap\ObserverFactory;
use Cspray\AnnotatedContainer\Bootstrap\ParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\XmlBootstrappingConfiguration;
use Cspray\AnnotatedContainer\Compile\CompositeDefinitionProvider;
use Cspray\AnnotatedContainer\Compile\DefinitionProvider;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Exception\InvalidBootstrapConfiguration;
use Cspray\AnnotatedContainer\Internal\CompositeLogger;
use Cspray\AnnotatedContainer\Internal\FileLogger;
use Cspray\AnnotatedContainer\Internal\StdoutLogger;
use Cspray\AnnotatedContainer\Unit\Helper\FixtureBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Unit\Helper\StubBootstrapObserverWithDependencies;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProviderWithDependencies;
use Cspray\AnnotatedContainer\Unit\Helper\StubParameterStore;
use Cspray\AnnotatedContainerFixture\Fixtures;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use PHPUnit\Framework\TestCase;
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
        $this->expectException(InvalidBootstrapConfiguration::class);
        $this->expectExceptionMessage(
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

    public function testValidXmlReturnsDefinitionProvider() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
    <definitionProviders>
        <definitionProvider>Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider</definitionProvider>
    </definitionProviders>
</annotatedContainer>
XML;
        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $configuration = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );
        $provider = $configuration->getContainerDefinitionProvider();
        self::assertInstanceOf(
            CompositeDefinitionProvider::class,
            $provider
        );
        self::assertContainsOnlyInstancesOf(
            StubDefinitionProvider::class,
            $provider->getDefinitionProviders()
        );
    }

    public function testDefinitionProviderNotClass() : void {
        $badXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
    <definitionProviders>
        <definitionProvider>FooBar</definitionProvider>
    </definitionProviders>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($badXml)
            ->at($this->vfs);

        $this->expectException(InvalidBootstrapConfiguration::class);
        $this->expectExceptionMessage(
            'All entries in definitionProviders must be classes that implement ' . DefinitionProvider::class
        );

        new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );
    }

    public function testDefinitionProviderNotImplementCorrectInterface() : void {
        $badXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
    <definitionProviders>
        <definitionProvider>Cspray\AnnotatedContainer\XmlBootstrappingConfiguration</definitionProvider>
    </definitionProviders>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($badXml)
            ->at($this->vfs);

        $this->expectException(InvalidBootstrapConfiguration::class);
        $this->expectExceptionMessage(
            'All entries in definitionProviders must be classes that implement ' . DefinitionProvider::class
        );

        new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver()
        );
    }

    public function testDefinitionProviderEmptyIfNoneDefined() : void {
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

        self::assertNull($config->getContainerDefinitionProvider());
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
    <parameterStore>Cspray\AnnotatedContainer\Unit\Helper\StubParameterStore</parameterStore>
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
    <parameterStore>something not a class</parameterStore>
  </parameterStores>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($badXml)
            ->at($this->vfs);

        $this->expectException(InvalidBootstrapConfiguration::class);
        $this->expectExceptionMessage(
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
    <parameterStore>Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider</parameterStore>
  </parameterStores>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($badXml)
            ->at($this->vfs);

        $this->expectException(InvalidBootstrapConfiguration::class);
        $this->expectExceptionMessage(
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
    <parameterStore>Cspray\AnnotatedContainer\Unit\Helper\StubParameterStoreWithDependencies</parameterStore>
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

    public function testDefinitionProviderFactoryPresentRespected() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <definitionProviders>
    <definitionProvider>Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProviderWithDependencies</definitionProvider>
  </definitionProviders>
</annotatedContainer>
XML;

        $consumerFactory = new class implements DefinitionProviderFactory {
            public function createProvider(string $identifier) : DefinitionProvider {
                return new $identifier(Fixtures::thirdPartyServices()->fooInterface());
            }
        };

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new FixtureBootstrappingDirectoryResolver(),
            definitionProviderFactory: $consumerFactory
        );

        $provider = $config->getContainerDefinitionProvider();
        self::assertInstanceOf(CompositeDefinitionProvider::class, $provider);
        self::assertContainsOnlyInstancesOf(StubDefinitionProviderWithDependencies::class, $provider->getDefinitionProviders());
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
    <observer>something not a class</observer>
  </observers>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($badXml)
            ->at($this->vfs);

        $this->expectException(InvalidBootstrapConfiguration::class);
        $this->expectExceptionMessage(
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
    <observer>Cspray\AnnotatedContainer\Helper\StubDefinitionProvider</observer>
  </observers>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($badXml)
            ->at($this->vfs);

        $this->expectException(InvalidBootstrapConfiguration::class);
        $this->expectExceptionMessage(
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
  <observers>
    <observer>Cspray\AnnotatedContainer\Unit\Helper\StubBootstrapObserverWithDependencies</observer>
  </observers>
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

    public function testVendorScanDirectoriesIncludedInList() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
            <dir>test/helper</dir>
            <dir>lib</dir>
        </source>
        <vendor>
          <package>
            <name>package/one</name>
            <source>
              <dir>src</dir>
              <dir>lib</dir>
            </source>
          </package>
          <package>
            <name>package/two</name>
            <source>
              <dir>other_src</dir>
            </source>
          </package>
        </vendor>
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
            ['src', 'test/helper', 'lib', 'vendor/package/one/src', 'vendor/package/one/lib', 'vendor/package/two/other_src'],
            $configuration->getScanDirectories()
        );
    }
}