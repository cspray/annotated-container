<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal\Interrogator;

use Cspray\AnnotatedContainer\InjectServiceDefinition;
use Generator;

final class InjectServiceDefinitionInterrogator {

    private array $UseServiceDefinitions;

    public function __construct(InjectServiceDefinition... $UseServiceDefinitions) {
        $this->UseServiceDefinitions = $UseServiceDefinitions;
    }

    public function gatherUseServiceDefinitions() : Generator {
        yield from $this->UseServiceDefinitions;
    }

}