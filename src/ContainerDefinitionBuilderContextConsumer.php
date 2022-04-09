<?php

namespace Cspray\AnnotatedContainer;

interface ContainerDefinitionBuilderContextConsumer {

    /**
     * @param ContainerDefinitionBuilderContext $context
     */
    public function consume(ContainerDefinitionBuilderContext $context) : void;

}