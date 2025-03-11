<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

interface BootstrappingDirectoryResolver {

    public function rootPath(string $subPath = '') : string;

    public function configurationPath(string $subPath = '') : string;
}
