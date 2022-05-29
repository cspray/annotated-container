<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;

class ContainerDefinitionCompileOptionsBuilderTest extends TestCase {

    public function testByDefaultContainerDefinitionBuilderContextConsumerIsNull() {
        $compilerOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build();

        $this->assertNull($compilerOptions->getContainerDefinitionBuilderContextConsumer());
    }

    public function testWithContextConsumerImmutable() {
        $a = ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath());
        $b = $a->withContainerDefinitionBuilderContextConsumer(new CallableContainerDefinitionBuilderContextConsumer(function() {}));

        $this->assertNotSame($a, $b);
    }

    public function testWithContextConsumerReturnsConsumer() {
        $compilerOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())
            ->withContainerDefinitionBuilderContextConsumer($expected = new CallableContainerDefinitionBuilderContextConsumer(function() {}))
            ->build();

        $this->assertSame($expected, $compilerOptions->getContainerDefinitionBuilderContextConsumer());
    }

}