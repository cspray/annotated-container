<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\ContainerFactoryNotFoundException;
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

    public function testContainerFactoryWithNoBackingContainerThrowsException() {
        $this->expectException(ContainerFactoryNotFoundException::class);
        $this->expectExceptionMessage('There is no backing Container library found. Please run "composer suggests" for supported containers.');
        containerFactory();
    }

}