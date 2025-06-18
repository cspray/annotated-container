<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\DeprecateObserversInFavorOfEventSystem;

/**
 * @deprecated
 */
#[DeprecateObserversInFavorOfEventSystem]
interface ObserverFactory {

    public function createObserver(string $observer) : PreAnalysisObserver|PostAnalysisObserver|ContainerCreatedObserver;
}
