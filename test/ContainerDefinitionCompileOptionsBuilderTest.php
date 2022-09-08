<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Compile\CallableDefinitionProvider;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ContainerDefinitionCompileOptionsBuilderTest extends TestCase {

    public function testByDefaultContainerDefinitionBuilderContextConsumerIsNull() : void {
        $compilerOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build();

        self::assertNull($compilerOptions->getDefinitionsProvider());
    }

    public function testByDefaultLoggerIsNull() : void {
        $compilerOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build();

        self::assertNull($compilerOptions->getLogger());
    }

    public function testWithContextConsumerImmutable() : void {
        $a = ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath());
        $b = $a->withContainerDefinitionBuilderContextConsumer(new CallableDefinitionProvider(function() {}));

        self::assertNotSame($a, $b);
    }

    public function testWithContextConsumerReturnsConsumer() : void {
        $compilerOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())
            ->withContainerDefinitionBuilderContextConsumer($expected = new CallableDefinitionProvider(function() {}))
            ->build();

        self::assertSame($expected, $compilerOptions->getDefinitionsProvider());
    }

    public function testWithLoggerImmutable() : void {
        $a = ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath());
        $b = $a->withLogger($this->getMockBuilder(LoggerInterface::class)->getMock());

        self::assertNotSame($a, $b);
    }

    public function testWithLoggerReturnsLogger() : void {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $compilerOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())
            ->withLogger($logger)
            ->build();

        self::assertSame($logger, $compilerOptions->getLogger());
    }

}