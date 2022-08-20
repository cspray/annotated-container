<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Compile;

use Cspray\AnnotatedContainer\AnnotatedContainerListener;
use Cspray\AnnotatedContainer\DefaultAnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\JsonContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use function Cspray\AnnotatedContainer\eventEmitter;

/**
 * A convenience builder to allow easily getting a ContainerDefinitionCompiler instance.
 */
final class ContainerDefinitionCompilerBuilder {

    private ?string $cacheDir = null;

    private function __construct() {}

    /**
     * With this option the results of the ContainerDefinition will be cached in a file stored in the provided
     * $cacheDir.
     *
     * If cached results are found in subsequent runs the static analysis parsing and conversion of AnnotatedTarget
     * to definition objects will be skipped.
     *
     * @param string $cacheDir
     * @return static
     */
    public static function withCache(string $cacheDir) : self {
        $instance = new self;
        $instance->cacheDir = $cacheDir;
        return $instance;
    }

    /**
     * With this option the results of the ContainerDefinition WILL NOT be cached.
     *
     * Using this option will cause the static analysis parsing and conversion of AnnotatedTarget to occur on every
     * run.
     *
     * @return static
     */
    public static function withoutCache() : self {
        return new self;
    }

    /**
     * Return the configured ContainerDefinitionCompiler
     *
     * @return ContainerDefinitionCompiler
     */
    public function build() : ContainerDefinitionCompiler {
        return $this->getCacheAppropriateCompiler();
    }

    private function getCacheAppropriateCompiler() : ContainerDefinitionCompiler {
        $phpParserCompiler = new AnnotatedTargetContainerDefinitionCompiler(
            new PhpParserAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
        if (!isset($this->cacheDir)) {
            return $phpParserCompiler;
        }
        return new CacheAwareContainerDefinitionCompiler(
            $phpParserCompiler,
            new ContainerDefinitionSerializer(),
            $this->cacheDir
        );
    }

}