<?php

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Bootstrap\RootDirectoryBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\VendorScanningThirdPartyInitializerProvider;
use Cspray\AnnotatedContainerFixture\VendorScanningInitializers\FirstInitializer;
use Cspray\AnnotatedContainerFixture\VendorScanningInitializers\SecondInitializer;
use Cspray\AnnotatedContainerFixture\VendorScanningInitializers\ThirdInitializer;
use PHPUnit\Framework\TestCase;

class VendorScanningThirdPartyInitializerProviderTest extends TestCase {

    public function testVendorScanningProviderIncludesCorrectClasses() : void {
        $directoryResolver = new RootDirectoryBootstrappingDirectoryResolver(
            __DIR__ . '/../../fixture_src/VendorScanningInitializers'
        );
        $subject = new VendorScanningThirdPartyInitializerProvider($directoryResolver);


        self::assertSame([
            FirstInitializer::class,
            SecondInitializer::class,
            ThirdInitializer::class
        ], $subject->getThirdPartyInitializers());
    }

}