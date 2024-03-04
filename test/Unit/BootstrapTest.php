<?php

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalytics;
use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalyticsObserver;
use Cspray\AnnotatedContainer\Bootstrap\ContainerCreatedObserver;
use Cspray\AnnotatedContainer\Bootstrap\DefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\ObserverFactory;
use Cspray\AnnotatedContainer\Bootstrap\ParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\PreAnalysisObserver;
use Cspray\AnnotatedContainer\Bootstrap\ServiceFromServiceDefinition;
use Cspray\AnnotatedContainer\Bootstrap\ServiceGatherer;
use Cspray\AnnotatedContainer\Bootstrap\ServiceWiringListener;
use Cspray\AnnotatedContainer\ContainerFactory\AurynContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Event\Listener\Bootstrap\AfterBootstrap;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Unit\Helper\FixtureBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Unit\Helper\StubAnalyticsObserver;
use Cspray\AnnotatedContainer\Unit\Helper\StubBootstrapListener;
use Cspray\AnnotatedContainer\Unit\Helper\StubBootstrapObserver;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProviderWithDependencies;
use Cspray\AnnotatedContainer\Unit\Helper\StubParameterStoreWithDependencies;
use Cspray\AnnotatedContainer\Unit\Helper\TestLogger;
use Cspray\AnnotatedContainerFixture\CustomServiceAttribute\Repository;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\PrecisionStopwatch\KnownIncrementingPreciseTime;
use Cspray\PrecisionStopwatch\Stopwatch;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use PHPUnit\Framework\TestCase;
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

        $bootstrap = new Bootstrap(
            new AurynContainerFactory(), directoryResolver: $directoryResolver
        );
        $container = $bootstrap->bootstrapContainer(Profiles::fromList(['default']));

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

        $bootstrap = new Bootstrap(
            new AurynContainerFactory(),
            directoryResolver: $directoryResolver
        );
        $bootstrap->bootstrapContainer(Profiles::fromList(['default']));
        $expected = md5(Fixtures::singleConcreteService()->getPath());

        self::assertFileExists('vfs://root/.annotated-container-cache/' . $expected);
    }

    public function testBootstrapWithValidDefinitionProvider() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>ThirdPartyServices</dir>
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

        $bootstrap = new Bootstrap(
            new AurynContainerFactory(),
            directoryResolver: $directoryResolver
        );
        $container = $bootstrap->bootstrapContainer(Profiles::fromList(['default']));

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
    <parameterStore>Cspray\AnnotatedContainer\Unit\Helper\StubParameterStore</parameterStore>
  </parameterStores>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $bootstrap = new Bootstrap(
            new AurynContainerFactory(),
            directoryResolver: $directoryResolver
        );
        $container = $bootstrap->bootstrapContainer(Profiles::fromList(['default']));

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

        $bootstrap = new Bootstrap(
            new AurynContainerFactory(),
            directoryResolver: $directoryResolver
        );
        $container = $bootstrap->bootstrapContainer(profiles: Profiles::fromList(['default', 'dev']));
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

        $bootstrap = new Bootstrap(
            new AurynContainerFactory(),
            directoryResolver: $directoryResolver
        );
        $container = $bootstrap->bootstrapContainer(profiles: Profiles::fromList(['default']), configurationFile: 'my-container.xml.dist');

        $service = $container->get(Fixtures::singleConcreteService()->fooImplementation()->getName());

        self::assertInstanceOf(
            Fixtures::singleConcreteService()->fooImplementation()->getName(),
            $service
        );
    }

    public function testBoostrapDefinitionProviderFactoryPassedToConfiguration() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>ThirdPartyServices</dir>
        </source>
    </scanDirectories>
    <definitionProviders>
        <definitionProvider>Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProviderWithDependencies</definitionProvider>
    </definitionProviders>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        $factory = new class implements DefinitionProviderFactory {

            public function createProvider(string $identifier) : DefinitionProvider {
                if ($identifier === StubDefinitionProviderWithDependencies::class) {
                    return new StubDefinitionProviderWithDependencies(Fixtures::thirdPartyServices()->fooImplementation());
                } else {
                    throw new \RuntimeException();
                }
            }
        };

        $container = (new Bootstrap(
            new AurynContainerFactory(),
            directoryResolver: $directoryResolver,
            definitionProviderFactory: $factory
        ))->bootstrapContainer(Profiles::fromList(['default']));

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
      <parameterStore>Cspray\AnnotatedContainer\Unit\Helper\StubParameterStoreWithDependencies</parameterStore>
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

        $container = (new Bootstrap(
            new AurynContainerFactory(),
            directoryResolver: $directoryResolver,
            parameterStoreFactory: $factory
        ))->bootstrapContainer(Profiles::fromList(['default']));

        $service = $container->get(Fixtures::injectCustomStoreServices()->scalarInjector()->getName());

        self::assertSame('ac-ackey', $service->key);
    }

    public function testServiceWiringObserver() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>AmbiguousAliasedServices</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $emitter = new Emitter();

        $bootstrap = new Bootstrap(
            new AurynContainerFactory($emitter),
            emitter: $emitter,
            directoryResolver: $directoryResolver
        );

        $listener = new class extends ServiceWiringListener {

            private ?AnnotatedContainer $container = null;
            private array $services = [];

            public function getAnnotatedContainer() : ?AnnotatedContainer {
                return $this->container;
            }

            public function getServices() : array {
                return $this->services;
            }

            protected function wireServices(AnnotatedContainer $container, ServiceGatherer $gatherer) : void {
                $this->container = $container;
                $this->services = $gatherer->getServicesForType(Fixtures::ambiguousAliasedServices()->fooInterface()->getName());
            }
        };

        $emitter->addAfterContainerCreationListener($listener);

        $container = $bootstrap->bootstrapContainer(Profiles::fromList(['default']));

        $actual = $listener->getServices();

        $actualServices = array_map(fn(ServiceFromServiceDefinition $fromServiceDefinition) => $fromServiceDefinition->getService(), $actual);

        usort($actualServices, fn($a, $b) => $a::class <=> $b::class);

        self::assertSame($container, $listener->getAnnotatedContainer());
        self::assertSame([
            $container->get(Fixtures::ambiguousAliasedServices()->barImplementation()->getName()),
            $container->get(Fixtures::ambiguousAliasedServices()->bazImplementation()->getName()),
            $container->get(Fixtures::ambiguousAliasedServices()->quxImplementation()->getName()),
        ], $actualServices);
    }

    public function testServiceWiringObserverByAttributes() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>CustomServiceAttribute</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $emitter = new Emitter();

        $bootstrap = new Bootstrap(
            new AurynContainerFactory($emitter),
            emitter: $emitter,
            directoryResolver: $directoryResolver
        );

        $listener = new class extends ServiceWiringListener {

            private ?AnnotatedContainer $container = null;
            private array $services = [];

            public function getAnnotatedContainer() : ?AnnotatedContainer {
                return $this->container;
            }

            public function getServices() : array {
                return $this->services;
            }

            protected function wireServices(AnnotatedContainer $container, ServiceGatherer $gatherer) : void {
                $this->container = $container;
                $this->services = $gatherer->getServicesWithAttribute(Repository::class);
            }
        };

        $emitter->addAfterContainerCreationListener($listener);

        $container = $bootstrap->bootstrapContainer(Profiles::fromList(['default', 'test']));

        $actual = $listener->getServices();
        $actualServices = array_map(fn(ServiceFromServiceDefinition $fromServiceDefinition) => $fromServiceDefinition->getService(), $actual);

        self::assertSame($container, $listener->getAnnotatedContainer());
        self::assertSame([
            $container->get(Fixtures::customServiceAttribute()->myRepo()->getName()),
        ], $actualServices);
    }

    public function testServiceWiringObserverByTypeProfileAware() : void {
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

        $emitter = new Emitter();

        $bootstrap = new Bootstrap(
            new AurynContainerFactory($emitter),
            emitter: $emitter,
            directoryResolver: $directoryResolver
        );

        $listener = new class extends ServiceWiringListener {

            private ?AnnotatedContainer $container = null;
            private array $services = [];

            public function getAnnotatedContainer() : ?AnnotatedContainer {
                return $this->container;
            }

            public function getServices() : array {
                return $this->services;
            }

            protected function wireServices(AnnotatedContainer $container, ServiceGatherer $gatherer) : void {
                $this->container = $container;
                $this->services = $gatherer->getServicesForType(Fixtures::profileResolvedServices()->fooInterface()->getName());
            }
        };

        $emitter->addAfterContainerCreationListener($listener);

        $container = $bootstrap->bootstrapContainer(Profiles::fromList(['default', 'prod']));

        $actual = $listener->getServices();

        $actualServices = array_map(fn(ServiceFromServiceDefinition $fromServiceDefinition) => $fromServiceDefinition->getService(), $actual);

        usort($actualServices, fn($a, $b) => $a::class <=> $b::class);

        self::assertSame($container, $listener->getAnnotatedContainer());
        self::assertSame([
            $container->get(Fixtures::profileResolvedServices()->prodImplementation()->getName()),
        ], $actualServices);
    }

    public function testServiceWiringObserverByAttributesProfileAware() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>CustomServiceAttribute</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $emitter = new Emitter();

        $bootstrap = new Bootstrap(
            new AurynContainerFactory($emitter),
            emitter: $emitter,
            directoryResolver: $directoryResolver
        );

        $listener = new class extends ServiceWiringListener {

            private ?AnnotatedContainer $container = null;
            private array $services = [];

            public function getAnnotatedContainer() : ?AnnotatedContainer {
                return $this->container;
            }

            public function getServices() : array {
                return $this->services;
            }

            protected function wireServices(AnnotatedContainer $container, ServiceGatherer $gatherer) : void {
                $this->container = $container;
                $this->services = $gatherer->getServicesWithAttribute(Repository::class);
            }
        };

        $emitter->addAfterContainerCreationListener($listener);

        // The Repository is only active under 'test' profile and should not be included
        $container = $bootstrap->bootstrapContainer(Profiles::fromList(['default', 'dev']));

        self::assertSame($container, $listener->getAnnotatedContainer());
        self::assertEmpty($listener->getServices());
    }

    public function testContainerAnalyticsHasExpectedTotalDuration() : void {
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

        $emitter = new Emitter();

        $listener = new class implements AfterBootstrap {
            private ?ContainerAnalytics $analytics = null;

            public function getAnalytics() : ?ContainerAnalytics {
                return $this->analytics;
            }

            public function handleAfterBootstrap(BootstrappingConfiguration $bootstrappingConfiguration, ContainerDefinition $containerDefinition, AnnotatedContainer $container, ContainerAnalytics $containerAnalytics,) : void {
                $this->analytics = $containerAnalytics;
            }
        };

        $subject = new Bootstrap(
            new AurynContainerFactory(),
            emitter: $emitter,
            directoryResolver: $directoryResolver,
            stopwatch: new Stopwatch(new KnownIncrementingPreciseTime())
        );

        $emitter->addAfterBootstrapListener($listener);

        $subject->bootstrapContainer(Profiles::fromList(['default']));

        $analytics = $listener->getAnalytics();
        self::assertNotNull($analytics);

        self::assertSame(3, $analytics->totalTime->timeTakenInNanoseconds());
        self::assertSame(1, $analytics->timePreppingForAnalysis->timeTakenInNanoseconds());
        self::assertSame(1, $analytics->timeTakenForAnalysis->timeTakenInNanoseconds());
        self::assertSame(1, $analytics->timeTakenCreatingContainer->timeTakenInNanoseconds());
    }

    public function testContainerFactoryPassedToConstructorTakesPriority() : void {
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

        $containerFactory = $this->getMockBuilder(ContainerFactory::class)->getMock();
        $containerFactory->expects($this->once())
            ->method('createContainer')
            ->willReturn($container = $this->getMockBuilder(AnnotatedContainer::class)->getMock());

        $subject = new Bootstrap(
            directoryResolver: $directoryResolver,
            containerFactory: $containerFactory
        );

        $actual = $subject->bootstrapContainer(Profiles::fromList(['default']));

        self::assertSame($container, $actual);
    }

    public function testBootstrapEventsTriggeredInCorrectOrder() : void {
        $emitter = new Emitter();
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

        $listener = new StubBootstrapListener();
        $emitter->addBeforeBootstrapListener($listener);
        $emitter->addAfterBootstrapListener($listener);

        $bootstrap = new Bootstrap(
            new AurynContainerFactory(),
            emitter: $emitter,
            directoryResolver: $directoryResolver
        );
        $bootstrap->bootstrapContainer(Profiles::fromList(['default']));

        self::assertSame(
            [StubBootstrapListener::class . '::handleBeforeBootstrap', StubBootstrapListener::class . '::handleAfterBootstrap'],
            $listener->getTriggeredEvents()
        );
    }

}