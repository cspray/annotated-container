<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use function Cspray\Typiphy\arrayType;

class ServiceCollectorParameterStoreTest extends TestCase {

    private function getContainerDefinitionCompiler() : ContainerDefinitionCompiler {
        return new AnnotatedTargetContainerDefinitionCompiler(
            new PhpParserAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
    }

    private function getContainerDefinition(string... $dir) : ContainerDefinition {
        $compiler = $this->getContainerDefinitionCompiler();
        $optionsBuilder = ContainerDefinitionCompileOptionsBuilder::scanDirectories(...$dir);
        return $compiler->compile($optionsBuilder->build());
    }

    public function testParameterStoreName() : void {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $subject = new ServiceCollectorParameterStore($container, []);

        $this->assertSame('service-collector', $subject->getName());
    }

    public function testNoServiceDefinitionsReturnsEmptyArray() : void {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $subject = new ServiceCollectorParameterStore($container, []);

        $this->assertSame([], $subject->fetch(arrayType(), $this::class));
    }

    public function testServiceDefinitionsPresentReturnsCorrectServices() : void {
        $containerDefinition = $this->getContainerDefinition(
            Fixtures::ambiguousAliasedServices()->getPath(),
            Fixtures::singleConcreteService()->getPath()
        );
        $container = containerFactory()->createContainer($containerDefinition);

        $collectedServices = (new ServiceCollectorParameterStore($container, [
            Fixtures::singleConcreteService()->fooImplementation()->getName(),
            Fixtures::ambiguousAliasedServices()->barImplementation()->getName(),
            Fixtures::ambiguousAliasedServices()->bazImplementation()->getName(),
            Fixtures::ambiguousAliasedServices()->quxImplementation()->getName()
        ]))
            ->fetch(arrayType(), Fixtures::ambiguousAliasedServices()->fooInterface()->getName());

        $expectedServices = [
            $container->get(Fixtures::ambiguousAliasedServices()->barImplementation()->getName()),
            $container->get(Fixtures::ambiguousAliasedServices()->bazImplementation()->getName()),
            $container->get(Fixtures::ambiguousAliasedServices()->quxImplementation()->getName())
        ];

        $this->assertSame($expectedServices, $collectedServices);
    }

}