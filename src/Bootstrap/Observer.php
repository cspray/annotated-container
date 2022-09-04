<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

interface Observer {

    public function beforeCompilation() : void;

    public function afterCompilation(ContainerDefinition $containerDefinition) : void;

    public function beforeContainerCreation(ContainerDefinition $containerDefinition) : void;

    public function afterContainerCreation(ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void;

}