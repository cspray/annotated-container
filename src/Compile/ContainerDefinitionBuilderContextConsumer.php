<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Compile;

/**
 * An interface that allows for making modifications to the ContainerDefinitionBuilder before the resultant
 * ContainerDefinition is built.
 */
interface ContainerDefinitionBuilderContextConsumer {

    /**
     * Add definitions to the $context->getBuilder(), then pass the new builder instance to $context->setBuilder().
     *
     * @param ContainerDefinitionBuilderContext $context
     */
    public function consume(ContainerDefinitionBuilderContext $context) : void;

}