<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Compile\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionCompilerBuilder;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;

final class ContainerDefinitionCompilerBuilderTest extends TestCase {

    private VirtualDirectory $vfs;

    protected function setUp() : void {
        $this->vfs = VirtualFilesystem::setup();
    }

    public function testInstanceWithoutCache() : void {
        $compiler = ContainerDefinitionCompilerBuilder::withoutCache()->build();

        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build()
        );

        self::assertCount(1, $containerDefinition->getServiceDefinitions());
    }

    public function testInstanceWithCache() : void {
        $compiler = ContainerDefinitionCompilerBuilder::withCache('vfs://root')->build();

        $containerDefinition = $compiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build()
        );

        self::assertCount(1, $containerDefinition->getServiceDefinitions());
        self::assertFileExists('vfs://root/' . md5(Fixtures::singleConcreteService()->getPath()));
    }

}