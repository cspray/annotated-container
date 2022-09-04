<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinitionBuilder;
use Cspray\AnnotatedContainer\Exception\InvalidServicePrepareDefinition;
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
        $this->expectException(InvalidServicePrepareDefinition::class);
        $this->expectExceptionMessage('A method for a ServicePrepareDefinition must not be blank.');
        ServicePrepareDefinitionBuilder::forMethod(objectType($this::class), '')->build();
    }

    public function testWithAttributeIsImmutable() : void {
        $prepareDefinition = ServicePrepareDefinitionBuilder::forMethod(
            Fixtures::interfacePrepareServices()->fooInterface(), 'setBar'
        );
        self::assertNotSame($prepareDefinition, $prepareDefinition->withAttribute(new ServicePrepare()));
    }

    public function testNoAttributeDefinitionAttributeIsNull() : void {
        $prepareDefinition = ServicePrepareDefinitionBuilder::forMethod(
            Fixtures::interfacePrepareServices()->fooInterface(), 'setBar'
        )->build();

        self::assertNull($prepareDefinition->getAttribute());
    }

    public function testWithAttributeDefinitionAttributeIsSameInstance() : void {
        $prepareDefinition = ServicePrepareDefinitionBuilder::forMethod(
            Fixtures::interfacePrepareServices()->fooInterface(), 'setBar'
        )->withAttribute($attr = new ServicePrepare())->build();

        self::assertSame($attr, $prepareDefinition->getAttribute());
    }
}