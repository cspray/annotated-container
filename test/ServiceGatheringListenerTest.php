<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Helper\StubServiceGatheringListener;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;

final class ServiceGatheringListenerTest extends TestCase {

    public function testNoServicesReturnsEmptyArray() : void {
        $subject = new StubServiceGatheringListener(Fixtures::implicitAliasedServices()->fooInterface());
        eventEmitter()->registerListener($subject);
        $containerDefinition = compiler()->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build()
        );
        containerFactory()->createContainer($containerDefinition);

        self::assertSame([] , $subject->getServices());
    }

    public function testSingleConcreteServiceReturnsOneItemArray() : void {
        $subject = new StubServiceGatheringListener(Fixtures::implicitAliasedServices()->fooInterface());
        eventEmitter()->registerListener($subject);
        $containerDefinition = compiler()->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::implicitAliasedServices()->getPath())->build()
        );
        $container = containerFactory()->createContainer($containerDefinition);

        self::assertSame([$container->get(Fixtures::implicitAliasedServices()->fooImplementation()->getName())], $subject->getServices());
    }

    public function testMultipleConcreteServices() : void {
        $subject = new StubServiceGatheringListener(Fixtures::ambiguousAliasedServices()->fooInterface());
        eventEmitter()->registerListener($subject);
        $containerDefinition = compiler()->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::ambiguousAliasedServices()->getPath())->build()
        );
        $container = containerFactory()->createContainer($containerDefinition);

        $services = $subject->getServices();
        usort($services, fn($a, $b) => $a::class <=> $b::class);

        self::assertSame([
            $container->get(Fixtures::ambiguousAliasedServices()->barImplementation()->getName()),
            $container->get(Fixtures::ambiguousAliasedServices()->bazImplementation()->getName()),
            $container->get(Fixtures::ambiguousAliasedServices()->quxImplementation()->getName())
        ], $services);
    }

}