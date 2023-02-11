<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\Observer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

class StubBootstrapObserver implements Observer {

    private array $invokedMethods = [];

    public function beforeCompilation(ActiveProfiles $activeProfiles) : void {
        $this->invokedMethods[] = [__METHOD__];
    }

    public function afterCompilation(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void {
        $this->invokedMethods[] = [__METHOD__];
    }

    public function beforeContainerCreation(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void {
        $this->invokedMethods[] = [__METHOD__];
    }

    public function afterContainerCreation(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        $this->invokedMethods[] = [__METHOD__];
    }

    public function getInvokedMethods() : array {
        return $this->invokedMethods;
    }
}