<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\ServiceDelegate;
use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\objectType;

class ServiceDelegateDefinitionBuilderTest extends TestCase {

    public function testWithEmptyDelegateMethodThrowsException() {
        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('The delegate method for a ServiceDelegateDefinition must not be blank.');
        ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface())
            ->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), '');
    }

    public function testWithDelegateMethodImmutableBuilder() {
        $builder1 = ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface());
        $builder2 = $builder1->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), 'createService');

        $this->assertNotSame($builder1, $builder2);
    }

    public function testBuildHasServiceDefinition() {
        $delegateDefinition = ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface())
            ->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), 'createService')
            ->build();

        $this->assertSame(Fixtures::delegatedService()->serviceInterface(), $delegateDefinition->getServiceType());
    }

    public function testBuildHasDelegateType() {
        $delegateDefinition = ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface())
            ->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), 'createService')
            ->build();

        $this->assertSame(Fixtures::delegatedService()->serviceFactory(), $delegateDefinition->getDelegateType());
    }

    public function testBuildHasDelegateMethod() {
        $delegateDefinition = ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface())
            ->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), 'createService')
            ->build();

        $this->assertSame('createService', $delegateDefinition->getDelegateMethod());
    }

}