<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

interface PostAnalysisObserver {

    public function notifyPostAnalysis(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void;


}