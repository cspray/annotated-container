<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface ContainerDefinitionBuilderContextConsumerFactory {

    public function createConsumer(string $identifier) : ContainerDefinitionBuilderContextConsumer;

}