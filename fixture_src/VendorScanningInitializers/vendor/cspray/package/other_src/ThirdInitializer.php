<?php

namespace Cspray\AnnotatedContainerFixture\VendorScanningInitializers;

use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializer;

class ThirdInitializer extends ThirdPartyInitializer {

    public function getRelativeScanDirectories() : array {
        return [];
    }

    public function getObserverClasses() : array {
        return [
            DependencyObserver::class
        ];
    }

    public function getDefinitionProviderClass() : ?string {
        return null;
    }

    public function getPackageName() : string {
        return 'cspray/package';
    }
}