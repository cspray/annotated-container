<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use PHPUnit\Framework\TestCase;

class UserApiFunctionsTest extends TestCase {

    public function testCompilerFunctionNoCache() {
        $compiler = compiler();

        $this->assertInstanceOf(AnnotatedTargetContainerDefinitionCompiler::class, $compiler);
    }

    public function testCompilerFunctionWithCache() {
        $compiler = compiler(sys_get_temp_dir());

        $this->assertInstanceOf(CacheAwareContainerDefinitionCompiler::class, $compiler);
    }

}