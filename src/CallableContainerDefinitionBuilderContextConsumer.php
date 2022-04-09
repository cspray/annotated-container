<?php

namespace Cspray\AnnotatedContainer;

class CallableContainerDefinitionBuilderContextConsumer implements ContainerDefinitionBuilderContextConsumer {

    private $callable;

    public function __construct(callable $callable) {
        $this->callable = $callable;
    }

    public function consume(ContainerDefinitionBuilderContext $context) : void {
        ($this->callable)($context);
    }

}