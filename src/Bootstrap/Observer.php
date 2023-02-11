<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

interface Observer {

    public function beforeCompilation(ActiveProfiles $activeProfiles) : void;

    public function afterCompilation(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void;

    public function beforeContainerCreation(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void;

    public function afterContainerCreation(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void;

}