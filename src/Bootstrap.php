<?php

namespace Cspray\AnnotatedContainer;

final class Bootstrap {

    private readonly BootstrappingDirectoryResolver $directoryResolver;

    public function __construct(
        BootstrappingDirectoryResolver $directoryResolver = null
    ) {
        $this->directoryResolver = $directoryResolver ?? $this->getDefaultDirectoryResolver();
    }

    /**
     * @throws Exception\ContainerFactoryNotFoundException
     * @throws Exception\InvalidCompileOptionsException
     * @throws Exception\InvalidAnnotationException
     * @throws Exception\InvalidBootstrappingConfigurationException
     */
    public function bootstrapContainer(
        array $profiles = ['default'],
        string $configurationFile = 'annotated-container.xml'
    ) : AnnotatedContainer {
        $configFile = $this->directoryResolver->getConfigurationPath($configurationFile);
        $configuration = new XmlBootstrappingConfiguration($configFile);

        $scanPaths = [];
        foreach ($configuration->getScanDirectories() as $scanDirectory) {
            $scanPaths[] = $this->directoryResolver->getSourceScanPath($scanDirectory);
        }
        $compileOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(...$scanPaths);
        $containerDefinitionConsumer = $configuration->getContainerDefinitionConsumer();
        if (isset($containerDefinitionConsumer)) {
            $compileOptions = $compileOptions->withContainerDefinitionBuilderContextConsumer($containerDefinitionConsumer);
        }

        $cacheDir = null;
        $configuredCacheDir = $configuration->getCacheDirectory();
        if (isset($configuredCacheDir)) {
            $cacheDir = $this->directoryResolver->getCachePath($configuredCacheDir);
        }
        $containerDefinition = compiler($cacheDir)->compile($compileOptions->build());

        $factoryOptions = ContainerFactoryOptionsBuilder::forActiveProfiles(...$profiles)->build();

        foreach ($configuration->getParameterStores() as $parameterStore) {
            containerFactory()->addParameterStore($parameterStore);
        }

        return containerFactory()->createContainer($containerDefinition, $factoryOptions);
    }

    private function getDefaultDirectoryResolver() : BootstrappingDirectoryResolver {
        $rootDir = dirname(__DIR__);
        if (!file_exists($rootDir . '/vendor/autoload.php')) {
            $rootDir = dirname(__DIR__, 5);
        }

        return new RootDirectoryBootstrappingDirectoryResolver($rootDir);
    }

}