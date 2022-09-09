<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\Observer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

final class StubBootstrapObserverWithDependencies implements Observer {

    public function __construct(public readonly string $myString) {}

    public function beforeCompilation() : void {
        // TODO: Implement beforeCompilation() method.
    }

    public function afterCompilation(ContainerDefinition $containerDefinition) : void {
        // TODO: Implement afterCompilation() method.
    }

    public function beforeContainerCreation(ContainerDefinition $containerDefinition) : void {
        // TODO: Implement beforeContainerCreation() method.
    }

    public function afterContainerCreation(ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        // TODO: Implement afterContainerCreation() method.
    }
}