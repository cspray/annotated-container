<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

/**
 * @deprecated
 */
interface PostAnalysisObserver {

    public function notifyPostAnalysis(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void;


}