<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\InvalidAliasException;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;

final class StandardAliasDefinitionResolverTest extends TestCase {

    public function testPassAbstractServiceDefinitionWithNoConcreteDefinitionReturnsCorrectResolution() : void {
        $subject = new StandardAliasDefinitionResolver();
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())
            ->build();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($serviceDefinition)
            ->build();

        $resolution = $subject->resolveAlias($containerDefinition, $serviceDefinition->getType());

        self::assertSame(AliasResolutionReason::NoConcreteService, $resolution->getAliasResolutionReason());
        self::assertNull($resolution->getAliasDefinition());
    }

    public function testPassAbstractServiceDefinitionWithSingleConcreteDefinitionReturnsCorrectResolution() : void {
        $subject = new StandardAliasDefinitionResolver();
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())
            ->build();
        $concrete1 = ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())
            ->build();
        $concrete2 = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->barImplementation())
            ->build();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($serviceDefinition)
            ->withServiceDefinition($concrete1)
            ->withServiceDefinition($concrete2)
            ->withAliasDefinition(
                $aliasDefinition = AliasDefinitionBuilder::forAbstract($serviceDefinition->getType())
                    ->withConcrete($concrete2->getType())
                    ->build()
            )->build();

        $resolution = $subject->resolveAlias($containerDefinition, $serviceDefinition->getType());

        self::assertSame(AliasResolutionReason::SingleConcreteService, $resolution->getAliasResolutionReason());
        self::assertSame($aliasDefinition, $resolution->getAliasDefinition());
    }

    public function testPassAbstractServiceDefinitionWithMultipleConcreteDefinitionReturnsCorrectResolution() : void {
        $subject = new StandardAliasDefinitionResolver();
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())
            ->build();
        $concrete1 = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->bazImplementation())
            ->build();
        $concrete2 = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->barImplementation())
            ->build();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($serviceDefinition)
            ->withServiceDefinition($concrete1)
            ->withServiceDefinition($concrete2)
            ->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract($serviceDefinition->getType())
                    ->withConcrete($concrete1->getType())
                    ->build()
            )->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract($serviceDefinition->getType())
                    ->withConcrete($concrete2->getType())
                    ->build()
            )->build();

        $resolution = $subject->resolveAlias($containerDefinition, $serviceDefinition->getType());

        self::assertSame(AliasResolutionReason::MultipleConcreteService, $resolution->getAliasResolutionReason());
        self::assertNull($resolution->getAliasDefinition());
    }

    public function testPassAbstractServiceDefinitionWithMultipleConcreteDefinitionWithPrimaryReturnsCorrectResolution() : void {
        $subject = new StandardAliasDefinitionResolver();
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())
            ->build();
        $concrete1 = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->bazImplementation())
            ->build();
        $concrete2 = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->barImplementation(), true)
            ->build();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($serviceDefinition)
            ->withServiceDefinition($concrete1)
            ->withServiceDefinition($concrete2)
            ->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract($serviceDefinition->getType())
                    ->withConcrete($concrete1->getType())
                    ->build()
            )->withAliasDefinition(
                $aliasDefinition = AliasDefinitionBuilder::forAbstract($serviceDefinition->getType())
                    ->withConcrete($concrete2->getType())
                    ->build()
            )->build();

        $resolution = $subject->resolveAlias($containerDefinition, $serviceDefinition->getType());

        self::assertSame(AliasResolutionReason::ConcreteServiceIsPrimary, $resolution->getAliasResolutionReason());
        self::assertSame($aliasDefinition, $resolution->getAliasDefinition());
    }

    public function testDelegatedAbstractServiceHasNoAlias() : void {
        $subject = new StandardAliasDefinitionResolver();

        $abstract = ServiceDefinitionBuilder::forAbstract(
            Fixtures::delegatedService()->serviceInterface()
        )->build();
        $concrete = ServiceDefinitionBuilder::forConcrete(
            Fixtures::delegatedService()->fooService()
        )->build();
        $alias = AliasDefinitionBuilder::forAbstract($abstract->getType())->withConcrete($concrete->getType())->build();
        $delegate = ServiceDelegateDefinitionBuilder::forService($abstract->getType())
            ->withDelegateMethod(
                Fixtures::delegatedService()->serviceFactory(),
                'createService'
            )->build();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($abstract)
            ->withServiceDefinition($concrete)
            ->withAliasDefinition($alias)
            ->withServiceDelegateDefinition($delegate)
            ->build();

        $resolution = $subject->resolveAlias($containerDefinition, $abstract->getType());

        self::assertNull($resolution->getAliasDefinition());
        self::assertSame(AliasResolutionReason::ServiceIsDelegated, $resolution->getAliasResolutionReason());
    }

}