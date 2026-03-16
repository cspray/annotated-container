<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\DeprecateObserversInFavorOfEventSystem;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

/**
 * @deprecated
 */
#[DeprecateObserversInFavorOfEventSystem]
interface PostAnalysisObserver {

    public function notifyPostAnalysis(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void;
}
