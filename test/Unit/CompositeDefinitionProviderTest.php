<?php

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Compile\CompositeDefinitionProvider;
use Cspray\AnnotatedContainer\Compile\DefinitionProvider;
use Cspray\AnnotatedContainer\Compile\DefinitionProviderContext;
use PHPUnit\Framework\TestCase;
final class CompositeDefinitionProviderTest extends TestCase {

    public function testProvidedDefinitionProvidersHaveConsumeCalled() : void {
        $context = $this->getMockBuilder(DefinitionProviderContext::class)->getMock();
        $providerOne = $this->getMockBuilder(DefinitionProvider::class)->getMock();
        $providerTwo = $this->getMockBuilder(DefinitionProvider::class)->getMock();

        $subject = new CompositeDefinitionProvider($providerOne, $providerTwo);

        $providerOne->expects($this->once())
            ->method('consume')
            ->with($context);

        $providerTwo->expects($this->once())
            ->method('consume')
            ->with($context);

        $subject->consume($context);
    }

}
