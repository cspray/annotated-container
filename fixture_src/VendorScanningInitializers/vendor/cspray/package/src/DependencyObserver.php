<?php

namespace Cspray\AnnotatedContainerFixture\VendorScanningInitializers;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\Observer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

class DependencyObserver implements Observer {

    public function beforeCompilation() : void {
    }

    public function afterCompilation(ContainerDefinition $containerDefinition) : void {
    }

    public function beforeContainerCreation(ContainerDefinition $containerDefinition) : void {
    }

    public function afterContainerCreation(ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        $container->get(SomeService::class)->setSomething('called from observer');
    }
}