<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Auryn\Injector;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\CacheAwareContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContextConsumerFactory;
use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerFactory\AurynContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\PhpDiContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactoryOptionsBuilder;
use Cspray\AnnotatedContainer\DefaultAnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\Exception\ContainerFactoryNotFoundException;
use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;
use Cspray\AnnotatedContainer\ParameterStoreFactory;
use Cspray\AnnotatedContainer\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use DI\Container;
use Psr\Log\LoggerInterface;

final class Bootstrap {

    private readonly BootstrappingDirectoryResolver $directoryResolver;
    private readonly ?LoggerInterface $logger;
    private readonly ?ParameterStoreFactory $parameterStoreFactory;
    private readonly ?ContainerDefinitionBuilderContextConsumerFactory $containerDefinitionBuilderContextConsumerFactory;
    /**
     * @var list<Observer>
     */
    private array $observers = [];

    public function __construct(
        BootstrappingDirectoryResolver $directoryResolver = null,
        LoggerInterface $logger = null,
        ParameterStoreFactory $parameterStoreFactory = null,
        ContainerDefinitionBuilderContextConsumerFactory $containerDefinitionBuilderContextConsumerFactory = null
    ) {
        $this->directoryResolver = $directoryResolver ?? $this->getDefaultDirectoryResolver();
        $this->logger = $logger;
        $this->parameterStoreFactory = $parameterStoreFactory;
        $this->containerDefinitionBuilderContextConsumerFactory = $containerDefinitionBuilderContextConsumerFactory;
    }

    public function addObserver(Observer $observer) : void {
        $this->observers[] = $observer;
    }

    /**
     * @param list<string> $profiles
     * @throws ContainerFactoryNotFoundException
     * @throws InvalidCompileOptionsException
     * @throws InvalidAnnotationException
     */
    public function bootstrapContainer(
        array $profiles = ['default'],
        string $configurationFile = 'annotated-container.xml'
    ) : AnnotatedContainer {
        $configFile = $this->directoryResolver->getConfigurationPath($configurationFile);
        $configuration = new XmlBootstrappingConfiguration(
            $configFile,
            $this->directoryResolver,
            $this->parameterStoreFactory,
            $this->containerDefinitionBuilderContextConsumerFactory
        );

        $scanPaths = [];
        foreach ($configuration->getScanDirectories() as $scanDirectory) {
            $scanPaths[] = $this->directoryResolver->getSourceScanPath($scanDirectory);
        }
        $compileOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(...$scanPaths);
        $containerDefinitionConsumer = $configuration->getContainerDefinitionConsumer();
        if ($containerDefinitionConsumer !== null) {
            $compileOptions = $compileOptions->withContainerDefinitionBuilderContextConsumer($containerDefinitionConsumer);
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

        foreach ($this->observers as $observer) {
            $observer->beforeCompilation();
        }

        $containerDefinition = $this->getCompiler($cacheDir)->compile($compileOptions->build());

        foreach ($this->observers as $observer) {
            $observer->afterCompilation($containerDefinition);
        }

        $factoryOptions = ContainerFactoryOptionsBuilder::forActiveProfiles(...$profiles);

        $containerFactory = $this->getContainerFactory();

        foreach ($configuration->getParameterStores() as $parameterStore) {
            $containerFactory->addParameterStore($parameterStore);
        }

        if ($logger !== null && $profilesAllowLogging) {
            $factoryOptions = $factoryOptions->withLogger($logger);
        }

        foreach ($this->observers as $observer) {
            $observer->beforeContainerCreation($containerDefinition);
        }
        $container = $containerFactory->createContainer($containerDefinition, $factoryOptions->build());
        foreach ($this->observers as $observer) {
            $observer->afterContainerCreation($containerDefinition, $container);
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

    private function getContainerFactory() : ContainerFactory {
        if (class_exists(Injector::class)) {
            return new AurynContainerFactory();

        } else if (class_exists(Container::class)) {
            return new PhpDiContainerFactory();
        } else {
            throw new ContainerFactoryNotFoundException('There is no backing Container library found. Please run "composer suggests" for supported containers.');
        }
    }

    private function getCompiler(?string $cacheDir) : ContainerDefinitionCompiler {
        $compiler = new AnnotatedTargetContainerDefinitionCompiler(
            new PhpParserAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
        if ($cacheDir !== null) {
            $compiler = new CacheAwareContainerDefinitionCompiler($compiler, new ContainerDefinitionSerializer(), $cacheDir);
        }

        return $compiler;
    }

}