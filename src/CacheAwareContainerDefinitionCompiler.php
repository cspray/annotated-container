<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\InvalidCacheException;

/**
 * A ContainerDefinitionCompiler decorator that allows for a ContainerDefinition to be serialized and cached to the
 * filesystem; this could potentially save time on very large codebase or be used when building production to not
 * require Container compilation on every request.
 */
final class CacheAwareContainerDefinitionCompiler implements ContainerDefinitionCompiler {

    private ContainerDefinitionCompiler $containerDefinitionCompiler;
    private ContainerDefinitionSerializer $containerDefinitionSerializer;
    private string $cacheDir;

    /**
     * @param ContainerDefinitionCompiler $containerDefinitionCompiler The compiler to use if the cache file is not present
     * @param ContainerDefinitionSerializer $containerDefinitionSerializer The serializer to serialize/deserialize the cached ContainerDefinition
     * @param string $cacheDir The directory that the cache files should be generated
     */
    public function __construct(ContainerDefinitionCompiler $containerDefinitionCompiler, ContainerDefinitionSerializer $containerDefinitionSerializer, string $cacheDir) {
        $this->containerDefinitionCompiler = $containerDefinitionCompiler;
        $this->containerDefinitionSerializer = $containerDefinitionSerializer;
        $this->cacheDir = $cacheDir;
    }

    /**
     * Will generate a ContainerDefinition from a serialized cache file.
     *
     * If the cached file is not present will generate a ContainerDefinition from the passed ContainerDefinitionCompiler
     * and save it to the $cacheDir based off of the directories to scan and the active profiles for the given compile
     * options.
     *
     * Please see bin/annotated-container compile --help for more information on pre-generating the cached ContainerDefinition.
     *
     * @param ContainerDefinitionCompileOptions $containerDefinitionCompileOptions
     * @return ContainerDefinition
     * @throws Exception\InvalidAnnotationException
     * @throws Exception\InvalidCompileOptionsException
     * @throws InvalidCacheException
     */
    public function compile(ContainerDefinitionCompileOptions $containerDefinitionCompileOptions): ContainerDefinition {
        $cacheFile = $this->getCacheFile($containerDefinitionCompileOptions->getScanDirectories());
        if (is_file($cacheFile)) {
            $containerDefinition = $this->containerDefinitionSerializer->deserialize(file_get_contents($cacheFile));
            $logger = $containerDefinitionCompileOptions->getLogger();
            if ($logger !== null) {
                $logger->info(sprintf(
                    'Skipping Annotated Container compiling. Using cached definition from %s.',
                    $cacheFile
                ));
            }
        } else {
            $containerDefinition = $this->containerDefinitionCompiler->compile($containerDefinitionCompileOptions);
            $serialized = $this->containerDefinitionSerializer->serialize($containerDefinition);
            $contentWritten = @file_put_contents($cacheFile, $serialized);
            if (!$contentWritten) {
                throw new InvalidCacheException(sprintf('The cache directory, %s, could not be written to. Please ensure it exists and is writeable.', $this->cacheDir));
            }
        }
        return $containerDefinition;
    }

    private function getCacheFile(array $dirs) : string {
        sort($dirs);
        return sprintf(
            '%s/%s',
            $this->cacheDir,
            md5(join($dirs))
        );
    }
}