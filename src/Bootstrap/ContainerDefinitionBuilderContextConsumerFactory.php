<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Compile\ContainerDefinitionBuilderContextConsumer;

interface ContainerDefinitionBuilderContextConsumerFactory {

    public function createConsumer(string $identifier) : ContainerDefinitionBuilderContextConsumer;

}