<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

final class CallableContainerDefinitionBuilderContextConsumer implements ContainerDefinitionBuilderContextConsumer {

    private $callable;

    public function __construct(callable $callable) {
        $this->callable = $callable;
    }

    public function consume(ContainerDefinitionBuilderContext $context) : void {
        ($this->callable)($context);
    }

}