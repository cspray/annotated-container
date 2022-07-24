<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\Typiphy\ObjectType;

interface AliasDefinitionResolver {

    public function resolveAlias(
        ContainerDefinition $containerDefinition,
        ObjectType $abstractService
    ) : AliasDefinitionResolution;

}