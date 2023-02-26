<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Auryn\Injector;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\CacheAwareContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\DefaultAnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\ContainerFactory\AurynContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerFactory\PhpDiContainerFactory;
use Cspray\AnnotatedContainer\Exception\BackingContainerNotFound;
use Cspray\AnnotatedContainer\Exception\InvalidBootstrapConfiguration;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use Cspray\AnnotatedContainer\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use DI\Container;
use Psr\Log\LoggerInterface;

final class Bootstrap {

    private readonly BootstrappingDirectoryResolver $directoryResolver;
    private readonly ?LoggerInterface $logger;
    private readonly ?ParameterStoreFactory $parameterStoreFactory;
    private readonly ?DefinitionProviderFactory $definitionProviderFactory;
    private readonly ?ObserverFactory $observerFactory;
    /**
     * @var list<Observer>
     */
    private array $observers = [];

    public function __construct(
        BootstrappingDirectoryResolver $directoryResolver = null,
        LoggerInterface $logger = null,
        ParameterStoreFactory $parameterStoreFactory = null,
        DefinitionProviderFactory $definitionProviderFactory = null,
        ObserverFactory $observerFactory = null
    ) {
        $this->directoryResolver = $directoryResolver ?? $this->getDefaultDirectoryResolver();
        $this->logger = $logger;
        $this->parameterStoreFactory = $parameterStoreFactory;
        $this->definitionProviderFactory = $definitionProviderFactory;
        $this->observerFactory = $observerFactory;
    }

    public function addObserver(Observer $observer) : void {
        $this->observers[] = $observer;
    }

    /**
     * @param list<string> $profiles
     * @throws BackingContainerNotFound
     * @throws InvalidBootstrapConfiguration
     */
    public function bootstrapContainer(
        array $profiles = ['default'],
        string $configurationFile = 'annotated-container.xml'
    ) : AnnotatedContainer {
        $configFile = $this->directoryResolver->getConfigurationPath($configurationFile);
        $configuration = new XmlBootstrappingConfiguration(
            $configFile,
            directoryResolver: $this->directoryResolver,
            parameterStoreFactory: $this->parameterStoreFactory,
            observerFactory: $this->observerFactory,
            definitionProviderFactory: $this->definitionProviderFactory
        );
        $activeProfiles = new class($profiles) implements ActiveProfiles {
            public function __construct(
                /** @var list<string> */
                private readonly array $profiles
            ) {}

            public function getProfiles() : array {
                return $this->profiles;
            }

            public function isActive(string $profile) : bool {
                return in_array($profile, $this->profiles, true);
            }
        };

        $scanPaths = [];
        foreach ($configuration->getScanDirectories() as $scanDirectory) {
            $scanPaths[] = $this->directoryResolver->getPathFromRoot($scanDirectory);
        }
        $compileOptions = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(...$scanPaths);
        $containerDefinitionConsumer = $configuration->getContainerDefinitionProvider();
        if ($containerDefinitionConsumer !== null) {
            $compileOptions = $compileOptions->withDefinitionProvider($containerDefinitionConsumer);
        }

        $profilesAllowLogging = count(array_intersect($profiles, $configuration->getLoggingExcludedProfiles())) === 0;
        $logger = $this->logger ?? $configuration->getLogger();
        if ($logger !== null && $profilesAllowLogging) {
            $compileOptions = $compileOptions->withLogger($logger);
        }

        $cacheDir = null;
        $configuredCacheDir = $configuration->getCacheDirectory();
        if ($configuredCacheDir !== null) {
            $cacheDir = $this->directoryResolver->getCachePath($configuredCacheDir);
        }

        foreach ($configuration->getObservers() as $observer) {
            $this->addObserver($observer);
        }

        foreach ($this->observers as $observer) {
            $observer->beforeCompilation($activeProfiles);
        }

        $containerDefinition = $this->getCompiler($cacheDir)->analyze($compileOptions->build());

        foreach ($this->observers as $observer) {
            $observer->afterCompilation($activeProfiles, $containerDefinition);
        }

        $factoryOptions = ContainerFactoryOptionsBuilder::forActiveProfiles(...$profiles);

        $containerFactory = $this->getContainerFactory($activeProfiles);

        foreach ($configuration->getParameterStores() as $parameterStore) {
            $containerFactory->addParameterStore($parameterStore);
        }

        if ($logger !== null && $profilesAllowLogging) {
            $factoryOptions = $factoryOptions->withLogger($logger);
        }

        foreach ($this->observers as $observer) {
            $observer->beforeContainerCreation($activeProfiles, $containerDefinition);
        }
        $container = $containerFactory->createContainer($containerDefinition, $factoryOptions->build());
        foreach ($this->observers as $observer) {
            $observer->afterContainerCreation($activeProfiles, $containerDefinition, $container);
        }
        return $container;
    }

    private function getDefaultDirectoryResolver() : BootstrappingDirectoryResolver {
        $rootDir = dirname(__DIR__);
        if (!file_exists($rootDir . '/vendor/autoload.php')) {
            $rootDir = dirname(__DIR__, 5);
        }

        return new RootDirectoryBootstrappingDirectoryResolver($rootDir);
    }

    private function getContainerFactory(ActiveProfiles $activeProfiles) : ContainerFactory {
        if (class_exists(Injector::class)) {
            return new AurynContainerFactory($activeProfiles);
        }

        if (class_exists(Container::class)) {
            return new PhpDiContainerFactory($activeProfiles);
        }

        throw BackingContainerNotFound::fromMissingImplementation();
    }

    private function getCompiler(?string $cacheDir) : ContainerDefinitionAnalyzer {
        $compiler = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
        if ($cacheDir !== null) {
            $compiler = new CacheAwareContainerDefinitionAnalyzer($compiler, new ContainerDefinitionSerializer(), $cacheDir);
        }

        return $compiler;
    }

}