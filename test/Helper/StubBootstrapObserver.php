<?php

namespace Cspray\AnnotatedContainer\Helper;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\Observer;
use Cspray\AnnotatedContainer\ContainerDefinition;

class StubBootstrapObserver implements Observer {

    private array $invokedMethods = [];

    public function beforeCompilation() : void {
        $this->invokedMethods[] = [__METHOD__];
    }

    public function afterCompilation(ContainerDefinition $containerDefinition) : void {
        $this->invokedMethods[] = [__METHOD__];
    }

    public function beforeContainerCreation(ContainerDefinition $containerDefinition) : void {
        $this->invokedMethods[] = [__METHOD__];
    }

    public function afterContainerCreation(ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        $this->invokedMethods[] = [__METHOD__];
    }

    public function getInvokedMethods() : array {
        return $this->invokedMethods;
    }
}