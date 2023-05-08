<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Auryn\Injector;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\CacheAwareContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
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
     * @var list<PreAnalysisObserver|PostAnalysisObserver|ContainerCreatedObserver>
     */
    private array $observers = [];

    public function __construct(
        BootstrappingDirectoryResolver $directoryResolver = null,
        LoggerInterface $logger = null,
        ParameterStoreFactory $parameterStoreFactory = null,
        DefinitionProviderFactory $definitionProviderFactory = null,
        ObserverFactory $observerFactory = null,
    ) {
        $this->directoryResolver = $directoryResolver ?? $this->defaultDirectoryResolver();
        $this->logger = $logger;
        $this->parameterStoreFactory = $parameterStoreFactory;
        $this->definitionProviderFactory = $definitionProviderFactory;
        $this->observerFactory = $observerFactory;
    }

    private function defaultDirectoryResolver() : BootstrappingDirectoryResolver {
        $rootDir = dirname(__DIR__);
        if (!file_exists($rootDir . '/vendor/autoload.php')) {
            $rootDir = dirname(__DIR__, 5);
        }

        return new RootDirectoryBootstrappingDirectoryResolver($rootDir);
    }

    public function addObserver(PreAnalysisObserver|PostAnalysisObserver|ContainerCreatedObserver $observer) : void {
        $this->observers[] = $observer;
    }

    /**
     * @param list<non-empty-string> $profiles
     * @throws BackingContainerNotFound
     * @throws InvalidBootstrapConfiguration
     */
    public function bootstrapContainer(
        array $profiles = ['default'],
        string $configurationFile = 'annotated-container.xml'
    ) : AnnotatedContainer {

        $configuration = $this->bootstrappingConfiguration($configurationFile);
        $activeProfiles = $this->activeProfiles($profiles);
        $analysisOptions = $this->analysisOptions($configuration, $activeProfiles);

        foreach ($configuration->getObservers() as $observer) {
            $this->addObserver($observer);
        }

        $this->notifyPreAnalysis($activeProfiles);

        $containerDefinition = $this->runStaticAnalysis($configuration, $analysisOptions);
        $this->notifyPostAnalysis($activeProfiles, $containerDefinition);

        $container = $this->createContainer(
            $configuration,
            $activeProfiles,
            $containerDefinition,
            $analysisOptions->getLogger()
        );

        $this->notifyContainerCreated($activeProfiles, $containerDefinition, $container);

        return $container;
    }

    private function bootstrappingConfiguration(string $configurationFile) : BootstrappingConfiguration {
        $configFile = $this->directoryResolver->getConfigurationPath($configurationFile);
        return new XmlBootstrappingConfiguration(
            $configFile,
            directoryResolver: $this->directoryResolver,
            parameterStoreFactory: $this->parameterStoreFactory,
            observerFactory: $this->observerFactory,
            definitionProviderFactory: $this->definitionProviderFactory
        );
    }

    /**
     * @param list<non-empty-string> $profiles
     * @return ActiveProfiles
     */
    private function activeProfiles(array $profiles) : ActiveProfiles {
        return new class($profiles) implements ActiveProfiles {
            public function __construct(
                /** @var list<non-empty-string> */
                private readonly array $profiles
            ) {}

            public function getProfiles() : array {
                return $this->profiles;
            }

            public function isActive(string $profile) : bool {
                return in_array($profile, $this->profiles, true);
            }
        };
    }

    private function analysisOptions(BootstrappingConfiguration $configuration, ActiveProfiles $activeProfiles) : ContainerDefinitionAnalysisOptions {
        $scanPaths = [];
        foreach ($configuration->getScanDirectories() as $scanDirectory) {
            $scanPaths[] = $this->directoryResolver->getPathFromRoot($scanDirectory);
        }
        $analysisOptions = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(...$scanPaths);
        $containerDefinitionConsumer = $configuration->getContainerDefinitionProvider();
        if ($containerDefinitionConsumer !== null) {
            $analysisOptions = $analysisOptions->withDefinitionProvider($containerDefinitionConsumer);
        }

        $profilesAllowLogging = count(array_intersect($activeProfiles->getProfiles(), $configuration->getLoggingExcludedProfiles())) === 0;
        $logger = $this->logger ?? $configuration->getLogger();
        if ($logger !== null && $profilesAllowLogging) {
            $analysisOptions = $analysisOptions->withLogger($logger);
        }

        return $analysisOptions->build();
    }

    private function notifyPreAnalysis(ActiveProfiles $activeProfiles) : void {
        foreach ($this->observers as $observer) {
            if ($observer instanceof PreAnalysisObserver) {
                $observer->notifyPreAnalysis($activeProfiles);
            }
        }
    }

    private function runStaticAnalysis(
        BootstrappingConfiguration $configuration,
        ContainerDefinitionAnalysisOptions $analysisOptions
    ) : ContainerDefinition {
        $cacheDir = null;
        $configuredCacheDir = $configuration->getCacheDirectory();
        if ($configuredCacheDir !== null) {
            $cacheDir = $this->directoryResolver->getCachePath($configuredCacheDir);
        }
        return $this->containerDefinitionAnalyzer($cacheDir)->analyze($analysisOptions);
    }

    private function containerDefinitionAnalyzer(?string $cacheDir) : ContainerDefinitionAnalyzer {
        $compiler = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter()
        );
        if ($cacheDir !== null) {
            $compiler = new CacheAwareContainerDefinitionAnalyzer($compiler, new ContainerDefinitionSerializer(), $cacheDir);
        }

        return $compiler;
    }

    private function notifyPostAnalysis(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void {
        foreach ($this->observers as $observer) {
            if ($observer instanceof PostAnalysisObserver) {
                $observer->notifyPostAnalysis($activeProfiles, $containerDefinition);
            }
        }
    }

    private function createContainer(
        BootstrappingConfiguration $configuration,
        ActiveProfiles $activeProfiles,
        ContainerDefinition $containerDefinition,
        ?LoggerInterface $logger
    ) : AnnotatedContainer {
        $factoryOptions = ContainerFactoryOptionsBuilder::forActiveProfiles(...$activeProfiles->getProfiles());

        $containerFactory = $this->containerFactory($activeProfiles);

        foreach ($configuration->getParameterStores() as $parameterStore) {
            $containerFactory->addParameterStore($parameterStore);
        }

        if ($logger !== null) {
            $factoryOptions = $factoryOptions->withLogger($logger);
        }

        return $containerFactory->createContainer($containerDefinition, $factoryOptions->build());
    }

    private function containerFactory(ActiveProfiles $activeProfiles) : ContainerFactory {
        if (class_exists(Injector::class)) {
            return new AurynContainerFactory($activeProfiles);
        }

        if (class_exists(Container::class)) {
            return new PhpDiContainerFactory($activeProfiles);
        }

        throw BackingContainerNotFound::fromMissingImplementation();
    }

    private function notifyContainerCreated(
        ActiveProfiles $activeProfiles,
        ContainerDefinition $containerDefinition,
        AnnotatedContainer $container
    ) : void {
        foreach ($this->observers as $observer) {
            if ($observer instanceof ContainerCreatedObserver) {
                $observer->notifyContainerCreated($activeProfiles, $containerDefinition, $container);
            }
        }
    }

}
