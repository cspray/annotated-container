<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\objectType;

class ServicePrepareDefinitionBuilderTest extends TestCase {

    public function testBuildHasService() {
        $prepareDefinition = ServicePrepareDefinitionBuilder::forMethod(Fixtures::interfacePrepareServices()->fooInterface(), 'setBar')->build();

        $this->assertSame(Fixtures::interfacePrepareServices()->fooInterface(), $prepareDefinition->getService());
    }

    public function testBuildHasMethod() {
        $prepareDefinition = ServicePrepareDefinitionBuilder::forMethod(Fixtures::interfacePrepareServices()->fooInterface(), 'setBar')->build();

        $this->assertSame('setBar', $prepareDefinition->getMethod());
    }

    public function testExceptionThrownIfMethodEmpty() {
        $this->expectException(DefinitionBuilderException::class);
        $this->expectExceptionMessage('A method for a ServicePrepareDefinition must not be blank.');
        ServicePrepareDefinitionBuilder::forMethod(objectType($this::class), '')->build();
    }

}