<?php

namespace Cspray\AnnotatedContainerFixture\VendorScanningInitializers;

use Cspray\AnnotatedContainer\Compile\DefinitionProvider;
use Cspray\AnnotatedContainer\Compile\DefinitionProviderContext;
use function Cspray\AnnotatedContainer\service;
use function Cspray\Typiphy\objectType;

class DependencyDefinitionProvider implements DefinitionProvider {

    public function consume(DefinitionProviderContext $context) : void {
        service($context, objectType(SomeService::class));
    }
}