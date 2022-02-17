<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainer\Internal\Interrogator;

use Cspray\AnnotatedContainer\InjectScalarDefinition;
use Generator;

final class InjectScalarDefinitionInterrogator {

    private array $UseScalarDefinitions;

    public function __construct(InjectScalarDefinition... $UseScalarDefinitions) {
        $this->UseScalarDefinitions = $UseScalarDefinitions;
    }

    public function gatherUseScalarDefinitions() : Generator {
        yield from $this->UseScalarDefinitions;
    }

}