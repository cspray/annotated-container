<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

interface BootstrappingDirectoryResolver {

    public function getConfigurationPath(string $subPath) : string;

    public function getSourceScanPath(string $subPath) : string;

    public function getCachePath(string $subPath) : string;

    public function getLogPath(string $subPath) : string;

}