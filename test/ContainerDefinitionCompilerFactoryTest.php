<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use PHPUnit\Framework\TestCase;

class ContainerDefinitionCompilerFactoryTest extends TestCase {

    public function testWithoutCacheReturnsCorrectInstanceOf() {
        $compiler = ContainerDefinitionCompilerBuilder::withoutCache()->build();
        $this->assertInstanceOf(AnnotatedTargetContainerDefinitionCompiler::class, $compiler);
    }

    public function testWithCacheReturnsCorrectInstanceOf() {
        $compiler = ContainerDefinitionCompilerBuilder::withCache(sys_get_temp_dir())->build();
        $this->assertInstanceOf(CacheAwareContainerDefinitionCompiler::class, $compiler);
    }


}