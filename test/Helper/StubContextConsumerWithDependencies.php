<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Helper;

use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContext;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContextConsumer;
use Cspray\Typiphy\ObjectType;
use function Cspray\AnnotatedContainer\service;

final class StubContextConsumerWithDependencies implements ContainerDefinitionBuilderContextConsumer {

    public function __construct(private readonly ObjectType $service) {}

    public function consume(ContainerDefinitionBuilderContext $context) : void {
        service($context, $this->service);
    }
}