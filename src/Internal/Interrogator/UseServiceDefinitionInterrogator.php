<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Internal\Interrogator;

use Cspray\AnnotatedInjector\UseServiceDefinition;
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