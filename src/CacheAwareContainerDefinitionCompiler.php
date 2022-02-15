<?php

namespace Cspray\AnnotatedContainer;

use InvalidArgumentException;

final class CacheAwareContainerDefinitionCompiler implements ContainerDefinitionCompiler {

    private ContainerDefinitionCompiler $containerDefinitionCompiler;
    private ContainerDefinitionSerializer $containerDefinitionSerializer;
    private string $cacheDir;

    public function __construct(ContainerDefinitionCompiler $containerDefinitionCompiler, ContainerDefinitionSerializer $containerDefinitionSerializer, string $cacheDir) {
        $this->containerDefinitionCompiler = $containerDefinitionCompiler;
        $this->containerDefinitionSerializer = $containerDefinitionSerializer;
        $this->cacheDir = $cacheDir;
    }

    public function compileDirectory(string $environment, array|string $dirs): ContainerDefinition {
        $dirs = is_string($dirs) ? [$dirs] : $dirs;
        $cacheFile = $this->getCacheFile($environment, $dirs);
        if (is_file($cacheFile)) {
            $containerDefinition = $this->containerDefinitionSerializer->deserialize(file_get_contents($cacheFile));
        } else {
            $containerDefinition = $this->containerDefinitionCompiler->compileDirectory($environment, $dirs);
            $serialized = $this->containerDefinitionSerializer->serialize($containerDefinition);
            $contentWritten = @file_put_contents($cacheFile, $serialized);
            if (!$contentWritten) {
                throw new InvalidArgumentException(sprintf('The cache directory, %s, could not be written to. Please ensure it exists and is writeable.', $this->cacheDir));
            }
        }
        return $containerDefinition;
    }

    private function getCacheFile(string $environment, array $dirs) : string {
        return sprintf(
            '%s/%s',
            $this->cacheDir,
            md5($environment . join($dirs))
        );
    }
}