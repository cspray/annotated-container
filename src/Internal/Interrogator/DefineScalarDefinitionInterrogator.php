<?php declare(strict_types=1);


namespace Cspray\AnnotatedInjector\Internal\Interrogator;

use Cspray\AnnotatedInjector\DefineScalarDefinition;
use Generator;

final class DefineScalarDefinitionInterrogator {

    private array $defineScalarDefinitions;

    public function __construct(DefineScalarDefinition... $defineScalarDefinitions) {
        $this->defineScalarDefinitions = $defineScalarDefinitions;
    }

    public function gatherDefineScalarDefinitions() : Generator {
        yield from $this->defineScalarDefinitions;
    }

}