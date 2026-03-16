<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\DeprecateObserversInFavorOfEventSystem;

/**
 * @deprecated
 */
#[DeprecateObserversInFavorOfEventSystem]
interface ContainerAnalyticsObserver {

    public function notifyAnalytics(ContainerAnalytics $analytics) : void;
}
