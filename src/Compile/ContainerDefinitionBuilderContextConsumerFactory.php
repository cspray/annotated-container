<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Compile;

interface ContainerDefinitionBuilderContextConsumerFactory {

    public function createConsumer(string $identifier) : ContainerDefinitionBuilderContextConsumer;

}