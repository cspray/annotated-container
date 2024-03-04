<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\ContainerFactory;

use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasResolutionReason;
use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Profiles;

interface ServiceAliasResolution {

    public function handleServiceAliasResolution(Profiles $profiles, AliasDefinition $aliasDefinition, AliasResolutionReason $resolutionReason) : void;

}
