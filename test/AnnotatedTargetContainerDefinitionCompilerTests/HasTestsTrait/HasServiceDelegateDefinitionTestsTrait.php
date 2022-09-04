<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\HasTestsTrait;

use Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects\ExpectedServiceDelegate;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

trait HasServiceDelegateDefinitionTestsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    abstract protected function serviceDelegateProvider() : array;

    /**
     * @dataProvider serviceDelegateProvider
     */
    final public function testServiceDelegateDefinition(ExpectedServiceDelegate $expectedServiceDelegate) : void {
        $definition = null;
        foreach ($this->getSubject()->getServiceDelegateDefinitions() as $delegateDefinition) {
            if ($delegateDefinition->getServiceType() === $expectedServiceDelegate->service) {
                $definition = $delegateDefinition;
                break;
            }
        }

        $this->assertSame($expectedServiceDelegate->factory, $definition?->getDelegateType());
        $this->assertSame($expectedServiceDelegate->method, $definition?->getDelegateMethod());
    }

}