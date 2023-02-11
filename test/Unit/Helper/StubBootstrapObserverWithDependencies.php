<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\Observer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

final class StubBootstrapObserverWithDependencies implements Observer {

    public function __construct(public readonly string $myString) {}

    public function beforeCompilation(ActiveProfiles $activeProfiles) : void {
    }

    public function afterCompilation(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void {
    }

    public function beforeContainerCreation(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void {
    }

    public function afterContainerCreation(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
    }
}