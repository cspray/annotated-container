<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * A ContainerDefinitionBuilderContextConsumer that allows you to pass in an anonymous function or some other callable
 * without having to implement your own type.
 */
final class CallableContainerDefinitionBuilderContextConsumer implements ContainerDefinitionBuilderContextConsumer {

    private $callable;

    public function __construct(callable $callable) {
        $this->callable = $callable;
    }

    /**
     * @param ContainerDefinitionBuilderContext $context
     * @return void
     */
    public function consume(ContainerDefinitionBuilderContext $context) : void {
        ($this->callable)($context);
    }

}