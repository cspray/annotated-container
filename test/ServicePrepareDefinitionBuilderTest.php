<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\DummyApps\InterfaceServicePrepare;
use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\objectType;

class ServicePrepareDefinitionBuilderTest extends TestCase {

    public function testBuildHasService() {
        $prepareDefinition = ServicePrepareDefinitionBuilder::forMethod(objectType(InterfaceServicePrepare\FooInterface::class), 'setBar')->build();

        $this->assertSame(objectType(InterfaceServicePrepare\FooInterface::class), $prepareDefinition->getService());
    }

    public function testBuildHasMethod() {
        $prepareDefinition = ServicePrepareDefinitionBuilder::forMethod(objectType(InterfaceServicePrepare\FooInterface::class), 'setBar')->build();

        $this->assertSame('setBar', $prepareDefinition->getMethod());
    }

    public function testExceptionThrownIfMethodEmpty() {
        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('A method for a ServicePrepareDefinition must not be blank.');
        ServicePrepareDefinitionBuilder::forMethod(objectType($this::class), '')->build();
    }

}