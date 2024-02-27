<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

/**
 * @deprecated
 */
interface PreAnalysisObserver {

    public function notifyPreAnalysis(ActiveProfiles $activeProfiles) : void;

}