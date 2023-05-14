<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

interface ContainerAnalyticsObserver {

    public function notifyAnalytics(ContainerAnalytics $analytics) : void;

}
