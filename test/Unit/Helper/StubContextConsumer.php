<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\Compile\DefinitionProvider;
use Cspray\AnnotatedContainer\Compile\DefinitionProviderContext;
use Cspray\AnnotatedContainerFixture\Fixtures;
use function Cspray\AnnotatedContainer\service;

final class StubContextConsumer implements DefinitionProvider {

    public function consume(DefinitionProviderContext $context) : void {
        service($context, Fixtures::thirdPartyServices()->fooImplementation());
    }
}