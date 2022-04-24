<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use PHPUnit\Framework\TestCase;

class ContainerDefinitionCompilerFactoryTest extends TestCase {

    public function testWithoutCacheReturnsCorrectInstanceOf() {
        $compiler = ContainerDefinitionCompilerFactory::withoutCache()->getCompiler();
        $this->assertInstanceOf(AnnotatedTargetContainerDefinitionCompiler::class, $compiler);
    }

    public function testWithCacheReturnsCorrectInstanceOf() {
        $compiler = ContainerDefinitionCompilerFactory::withCache(sys_get_temp_dir())->getCompiler();
        $this->assertInstanceOf(CacheAwareContainerDefinitionCompiler::class, $compiler);
    }


}