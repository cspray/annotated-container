<?php

namespace Cspray\AnnotatedContainerFixture\VendorScanningInitializers;

use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use function Cspray\AnnotatedContainer\service;
use function Cspray\Typiphy\objectType;

class DependencyDefinitionProvider implements DefinitionProvider {

    public function consume(DefinitionProviderContext $context) : void {
        service($context, objectType(SomeService::class));
    }
}