<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Interrogator;

use Cspray\AnnotatedInjector\DefineServiceDefinition;
use Generator;

final class DefineServiceDefinitionInterrogator {

    private array $defineServiceDefinitions;

    public function __construct(DefineServiceDefinition... $defineServiceDefinitions) {
        $this->defineServiceDefinitions = $defineServiceDefinitions;
    }

    public function gatherDefineServiceDefinitions() : Generator {
        yield from $this->defineServiceDefinitions;
    }

}