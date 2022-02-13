<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal\Interrogator;

use Cspray\AnnotatedContainer\UseServiceDefinition;
use Generator;

final class UseServiceDefinitionInterrogator {

    private array $UseServiceDefinitions;

    public function __construct(UseServiceDefinition... $UseServiceDefinitions) {
        $this->UseServiceDefinitions = $UseServiceDefinitions;
    }

    public function gatherUseServiceDefinitions() : Generator {
        yield from $this->UseServiceDefinitions;
    }

}