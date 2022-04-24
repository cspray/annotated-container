<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\ServiceDelegate;
use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\objectType;

class ServiceDelegateDefinitionBuilderTest extends TestCase {

    public function testWithEmptyDelegateMethodThrowsException() {
        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('The delegate method for a ServiceDelegateDefinition must not be blank.');
        ServiceDelegateDefinitionBuilder::forService(objectType(ServiceDelegate\ServiceInterface::class))->withDelegateMethod(objectType(ServiceDelegate\ServiceFactory::class), '');
    }

    public function testWithDelegateMethodImmutableBuilder() {
        $builder1 = ServiceDelegateDefinitionBuilder::forService(objectType(ServiceDelegate\ServiceInterface::class));
        $builder2 = $builder1->withDelegateMethod(objectType(ServiceDelegate\ServiceFactory::class), 'createService');

        $this->assertNotSame($builder1, $builder2);
    }

    public function testBuildHasServiceDefinition() {
        $delegateDefinition = ServiceDelegateDefinitionBuilder::forService(objectType(ServiceDelegate\ServiceInterface::class))
            ->withDelegateMethod(objectType(ServiceDelegate\ServiceFactory::class), 'createService')
            ->build();

        $this->assertSame(objectType(ServiceDelegate\ServiceInterface::class), $delegateDefinition->getServiceType());
    }

    public function testBuildHasDelegateType() {
        $delegateDefinition = ServiceDelegateDefinitionBuilder::forService(objectType(ServiceDelegate\ServiceInterface::class))
            ->withDelegateMethod(objectType(ServiceDelegate\ServiceFactory::class), 'createService')
            ->build();

        $this->assertSame(objectType(ServiceDelegate\ServiceFactory::class), $delegateDefinition->getDelegateType());
    }

    public function testBuildHasDelegateMethod() {
        $delegateDefinition = ServiceDelegateDefinitionBuilder::forService(objectType(ServiceDelegate\ServiceInterface::class))
            ->withDelegateMethod(objectType(ServiceDelegate\ServiceFactory::class), 'createService')
            ->build();

        $this->assertSame('createService', $delegateDefinition->getDelegateMethod());
    }

}