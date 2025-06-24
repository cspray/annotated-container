<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap\Configuration;

use Cspray\AnnotatedContainer\Bootstrap\DirectoryResolver\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;

final class ContainerDefinitionAnalysisOptionsFromBootstrappingConfiguration {

    public function __construct(
        private readonly BootstrappingConfiguration $configuration,
        private readonly BootstrappingDirectoryResolver $directoryResolver
    ) {
    }

    public function create() : ContainerDefinitionAnalysisOptions {
        $scanPaths = [];
        foreach ($this->configuration->scanDirectories() as $scanDirectory) {
            $scanPaths[] = $this->directoryResolver->rootPath($scanDirectory);
        }
        /** @var list<non-empty-string> $scanPaths */
        $containerDefinitionConsumer = $this->configuration->containerDefinitionProvider();

        if ($containerDefinitionConsumer === null) {
            $analysisOptions = ContainerDefinitionAnalysisOptions::fromScanDirectories($scanPaths);
        } else {
            $analysisOptions = ContainerDefinitionAnalysisOptions::fromScanDirectoriesAndDefinitionProvider(
                $scanPaths,
                $containerDefinitionConsumer
            );
        }

        return $analysisOptions;
    }
}
