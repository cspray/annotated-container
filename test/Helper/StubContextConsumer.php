<?php

namespace Cspray\AnnotatedContainer\Helper;

use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContext;
use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContextConsumer;
use Cspray\AnnotatedContainerFixture\Fixtures;
use function Cspray\AnnotatedContainer\service;

final class StubContextConsumer implements ContainerDefinitionBuilderContextConsumer {

    public function consume(ContainerDefinitionBuilderContext $context) : void {
        service($context, Fixtures::thirdPartyServices()->fooImplementation());
    }
}