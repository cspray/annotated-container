<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

final class ContainerDefinitionCompilerFactory {

    private ?string $cacheDir = null;

    public static function withCache(string $cacheDir) : self {
        $instance = new self;
        $instance->cacheDir = $cacheDir;
        return $instance;
    }

    public static function withoutCache() : self {
        return new self;
    }

    public function getCompiler() : ContainerDefinitionCompiler {
        $phpParserCompiler = new AnnotatedTargetContainerDefinitionCompiler(
            new StaticAnalysisAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
        if (!isset($this->cacheDir)) {
            return $phpParserCompiler;
        }
        return new CacheAwareContainerDefinitionCompiler(
            $phpParserCompiler,
            new JsonContainerDefinitionSerializer(),
            $this->cacheDir
        );
    }

}