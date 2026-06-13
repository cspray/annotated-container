<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointDefinitionProvider;

/**
 * Represents configurable details for the compilation of a ContainerDefinition.
 */
final readonly class ContainerDefinitionAnalysisOptions {

    /**
     * @param list<non-empty-string> $scanDirectories
     * @param DefinitionProvider|null $definitionProvider
     */
    private function __construct(
        private array $scanDirectories,
        private ?DefinitionProvider $definitionProvider,
    ) {
    }

    /**
     * @param list<non-empty-string> $scanDirectories
     * @return self
     */
    public static function fromScanDirectories(array $scanDirectories) : self {
        return new self($scanDirectories, null);
    }

    /**
     * @param list<non-empty-string> $scanDirectories
     * @param DefinitionProvider $definitionProvider
     * @return self
     */
    public static function fromScanDirectoriesAndDefinitionProvider(
        array $scanDirectories,
        DefinitionProvider $definitionProvider,
    ) : self {
        return new self($scanDirectories, $definitionProvider);
    }

    /**
     * Return a list of directories to scan for annotated services.
     *
     * @return list<non-empty-string>
     */
    public function scanDirectories() : array {
        return $this->scanDirectories;
    }

    /**
     * If you need to modify the ContainerDefinitionBuilder return a proper consumer, otherwise null.
     *
     * This is the primary entrypoint for adding third-party services that can't be annotated to the container.
     *
     * @return DefinitionProvider|null
     */
    #[SingleEntrypointDefinitionProvider]
    public function definitionProvider() : ?DefinitionProvider {
        return $this->definitionProvider;
    }
}
