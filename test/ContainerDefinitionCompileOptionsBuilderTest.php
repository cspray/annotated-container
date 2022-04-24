<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use PHPUnit\Framework\TestCase;

class ContainerDefinitionCompileOptionsBuilderTest extends TestCase {

    public function testByDefaultContainerDefinitionBuilderContextConsumerIsNull() {
        $compilerOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleServices')->build();

        $this->assertNull($compilerOptions->getContainerDefinitionBuilderContextConsumer());
    }

    public function testWithContextConsumerImmutable() {
        $a = ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleServices');
        $b = $a->withContainerDefinitionBuilderContextConsumer(new CallableContainerDefinitionBuilderContextConsumer(function() {}));

        $this->assertNotSame($a, $b);
    }

    public function testWithContextConsumerReturnsConsumer() {
        $compilerOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(DummyAppUtils::getRootDir() . '/SimpleServices')
            ->withContainerDefinitionBuilderContextConsumer($expected = new CallableContainerDefinitionBuilderContextConsumer(function() {}))
            ->build();

        $this->assertSame($expected, $compilerOptions->getContainerDefinitionBuilderContextConsumer());
    }

}