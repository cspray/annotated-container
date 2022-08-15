<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\AliasResolution;

use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\AliasDefinitionResolution;
use Cspray\Typiphy\ObjectType;

interface AliasDefinitionResolver {

    public function resolveAlias(
        ContainerDefinition $containerDefinition,
        ObjectType $abstractService
    ) : AliasDefinitionResolution;

}