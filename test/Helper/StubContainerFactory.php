<?php

namespace Cspray\AnnotatedContainer\Helper;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactoryOptions;
use Cspray\AnnotatedContainer\ParameterStore;

final class StubContainerFactory implements ContainerFactory {

    public function __construct(
        private readonly AnnotatedContainer $container
    ) {}

    public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : AnnotatedContainer {
        return $this->container;
    }

    public function addParameterStore(ParameterStore $parameterStore) : void {
        // TODO: Implement addParameterStore() method.
    }
}