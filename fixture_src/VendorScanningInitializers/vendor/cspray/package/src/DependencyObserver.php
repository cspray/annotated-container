<?php

namespace Cspray\AnnotatedContainerFixture\VendorScanningInitializers;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\ContainerCreatedObserver;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

class DependencyObserver implements ContainerCreatedObserver {

    public function notifyContainerCreated(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        $container->get(SomeService::class)->setSomething('called from observer');
    }
}