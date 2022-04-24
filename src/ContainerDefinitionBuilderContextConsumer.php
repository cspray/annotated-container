<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface ContainerDefinitionBuilderContextConsumer {

    /**
     * @param ContainerDefinitionBuilderContext $context
     */
    public function consume(ContainerDefinitionBuilderContext $context) : void;

}