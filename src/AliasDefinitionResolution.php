<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface AliasDefinitionResolution {

    public function getAliasResolutionReason() : AliasResolutionReason;

    public function getAliasDefinition() : ?AliasDefinition;

}