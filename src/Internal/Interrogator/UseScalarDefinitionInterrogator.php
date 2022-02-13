<?php declare(strict_types=1);


namespace Cspray\AnnotatedInjector\Internal\Interrogator;

use Cspray\AnnotatedInjector\UseScalarDefinition;
use Generator;

final class UseScalarDefinitionInterrogator {

    private array $UseScalarDefinitions;

    public function __construct(UseScalarDefinition... $UseScalarDefinitions) {
        $this->UseScalarDefinitions = $UseScalarDefinitions;
    }

    public function gatherUseScalarDefinitions() : Generator {
        yield from $this->UseScalarDefinitions;
    }

}