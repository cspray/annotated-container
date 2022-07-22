<?php

namespace Cspray\AnnotatedContainer\Helper;

use Cspray\AnnotatedContainer\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;

final class FixtureBootstrappingDirectoryResolver implements BootstrappingDirectoryResolver {

    public function __construct() {
    }

    public function getConfigurationPath(string $subPath) : string {
        return sprintf('vfs://root/%s', $subPath);
    }

    public function getSourceScanPath(string $subPath) : string {
        return sprintf('%s/%s', Fixtures::getRootPath(), $subPath);
    }

    public function getCachePath(string $subPath) : string {
        return sprintf('vfs://root/%s', $subPath);
    }

}