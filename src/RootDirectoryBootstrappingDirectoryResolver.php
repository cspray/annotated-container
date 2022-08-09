<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

final class RootDirectoryBootstrappingDirectoryResolver implements BootstrappingDirectoryResolver {

    public function __construct(
        private readonly string $rootDir
    ) {}

    public function getConfigurationPath(string $subPath) : string {
        return sprintf('%s/%s', $this->rootDir, $subPath);
    }

    public function getSourceScanPath(string $subPath) : string {
        return sprintf('%s/%s', $this->rootDir, $subPath);
    }

    public function getCachePath(string $subPath) : string {
        return sprintf('%s/%s', $this->rootDir, $subPath);
    }

    public function getLogPath(string $subPath) : string {
        return sprintf('%s/%s', $this->rootDir, $subPath);
    }
}