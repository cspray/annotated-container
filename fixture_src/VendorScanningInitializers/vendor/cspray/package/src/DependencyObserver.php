<?php

namespace Cspray\AnnotatedContainerFixture\VendorScanningInitializers;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\Observer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

class DependencyObserver implements Observer {

    public function beforeCompilation(ActiveProfiles $activeProfiles) : void {
    }

    public function afterCompilation(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void {
    }

    public function beforeContainerCreation(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void {
    }

    public function afterContainerCreation(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        $container->get(SomeService::class)->setSomething('called from observer');
    }
}