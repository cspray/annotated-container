<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\DeprecateObserversInFavorOfEventSystem;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

/**
 * @deprecated
 */
#[DeprecateObserversInFavorOfEventSystem]
interface PreAnalysisObserver {

    public function notifyPreAnalysis(ActiveProfiles $activeProfiles) : void;
}
