<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContextConsumerFactory;
use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerFactoryOptionsBuilder;
use Cspray\AnnotatedContainer\Exception\ContainerFactoryNotFoundException;
use Cspray\AnnotatedContainer\Exception\InvalidAnnotationException;
use Cspray\AnnotatedContainer\Exception\InvalidCompileOptionsException;
use Cspray\AnnotatedContainer\ParameterStoreFactory;
use Psr\Log\LoggerInterface;
use function Cspray\AnnotatedContainer\compiler;
use function Cspray\AnnotatedContainer\containerFactory;

final class Bootstrap {

    private readonly BootstrappingDirectoryResolver $directoryResolver;
    private readonly ?LoggerInterface $logger;
    private readonly ?ParameterStoreFactory $parameterStoreFactory;
    private readonly ?ContainerDefinitionBuilderContextConsumerFactory $containerDefinitionBuilderContextConsumerFactory;

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

    /**
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
        $containerDefinition = compiler($cacheDir)->compile($compileOptions->build());

        $factoryOptions = ContainerFactoryOptionsBuilder::forActiveProfiles(...$profiles);

        foreach ($configuration->getParameterStores() as $parameterStore) {
            containerFactory()->addParameterStore($parameterStore);
        }

        if ($logger !== null && $profilesAllowLogging) {
            $factoryOptions = $factoryOptions->withLogger($logger);
        }

        return containerFactory()->createContainer($containerDefinition, $factoryOptions->build());
    }

    private function getDefaultDirectoryResolver() : BootstrappingDirectoryResolver {
        $rootDir = dirname(__DIR__);
        if (!file_exists($rootDir . '/vendor/autoload.php')) {
            $rootDir = dirname(__DIR__, 5);
        }

        return new RootDirectoryBootstrappingDirectoryResolver($rootDir);
    }

}