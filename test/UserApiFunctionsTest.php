<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\ContainerFactory\AurynContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\PhpDiContainerFactory;
use PHPUnit\Framework\TestCase;

class UserApiFunctionsTest extends TestCase {

    public function testCompilerFunctionNoCache() {
        $compiler = compiler();

        $this->assertInstanceOf(AnnotatedTargetContainerDefinitionCompiler::class, $compiler);
    }

    public function testCompilerFunctionWithCache() {
        $compiler = compiler(sys_get_temp_dir());

        self::assertInstanceOf(CacheAwareContainerDefinitionCompiler::class, $compiler);
    }

    public function testContainerFactoryDefaultsToAuryn() {
        $containerFactory = containerFactory();

        self::assertInstanceOf(AurynContainerFactory::class, $containerFactory);
    }

    public function testContainerFactoryRespectsPassingAurynExplicitly() {
        $containerFactory = containerFactory(SupportedContainers::Auryn);

        self::assertInstanceOf(AurynContainerFactory::class, $containerFactory);
    }

    public function testContainerFactoryRespectsGettingNonDefault() {
        $containerFactory = containerFactory(SupportedContainers::PhpDi);

        self::assertInstanceOf(PhpDiContainerFactory::class, $containerFactory);
    }

    public function supportedContainerProvider() : array {
        return [
            [SupportedContainers::Default],
            [SupportedContainers::Auryn],
            [SupportedContainers::PhpDi]
        ];
    }

    /**
     * @dataProvider supportedContainerProvider
     */
    public function testContainerFactoryReturnsSameInstance(SupportedContainers $container) {
        $a = containerFactory($container);
        $b = containerFactory($container);

        self::assertSame($a, $b);
    }

}