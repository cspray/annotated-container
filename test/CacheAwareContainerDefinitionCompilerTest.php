<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\Exception\InvalidCacheException;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class CacheAwareContainerDefinitionCompilerTest extends TestCase {

    private CacheAwareContainerDefinitionCompiler $cacheAwareContainerDefinitionCompiler;
    private AnnotatedTargetContainerDefinitionCompiler $phpParserContainerDefinitionCompiler;
    private ContainerDefinitionSerializer $containerDefinitionSerializer;
    private vfsStreamDirectory $root;

    protected function setUp(): void {
        $this->cacheAwareContainerDefinitionCompiler = new CacheAwareContainerDefinitionCompiler(
            $this->phpParserContainerDefinitionCompiler = new AnnotatedTargetContainerDefinitionCompiler(
                new PhpParserAnnotatedTargetParser(),
                new DefaultAnnotatedTargetDefinitionConverter()
            ),
            $this->containerDefinitionSerializer = new JsonContainerDefinitionSerializer(),
            'vfs://root'
        );
        $this->root = vfsStream::setup();
    }

    public function testFileDoesNotExistWritesFile() {
        $dir = Fixtures::singleConcreteService()->getPath();
        $containerDefinition = $this->cacheAwareContainerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories($dir)->build()
        );

        $this->assertNotNull($this->root->getChild('root/' . md5($dir)));

        $expected = $this->containerDefinitionSerializer->serialize($containerDefinition);
        $actual = $this->root->getChild('root/' . md5($dir))->getContent();

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testFileDoesExistDoesNotCallCompiler() {
        $dir = Fixtures::implicitAliasedServices()->getPath();
        $containerDefinition = $this->phpParserContainerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories($dir)->build()
        );
        $serialized = $this->containerDefinitionSerializer->serialize($containerDefinition);

        vfsStream::newFile(md5($dir))->at($this->root)->setContent($serialized);

        $mock = $this->getMockBuilder(ContainerDefinitionCompiler::class)->getMock();
        $mock->expects($this->never())->method('compile');
        $subject = new CacheAwareContainerDefinitionCompiler(
            $mock,
            $this->containerDefinitionSerializer,
            'vfs://root'
        );

        $containerDefinition = $subject->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories($dir)->build()
        );
        $actual = $this->containerDefinitionSerializer->serialize($containerDefinition);

        $this->assertJsonStringEqualsJsonString($serialized, $actual);
    }

    public function testMultipleDirectoriesCachedRegardlessOfOrder() {
        $implicitDir = Fixtures::implicitAliasedServices()->getPath();
        $concreteDir = Fixtures::singleConcreteService()->getPath();
        $containerDefinition = $this->phpParserContainerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories($concreteDir, $implicitDir)->build()
        );
        $serialized = $this->containerDefinitionSerializer->serialize($containerDefinition);

        vfsStream::newFile(md5($implicitDir . $concreteDir))->at($this->root)->setContent($serialized);

        $mock = $this->getMockBuilder(ContainerDefinitionCompiler::class)->getMock();
        $mock->expects($this->never())->method('compile');
        $subject = new CacheAwareContainerDefinitionCompiler(
            $mock,
            $this->containerDefinitionSerializer,
            'vfs://root'
        );

        $containerDefinition = $subject->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories($concreteDir, $implicitDir)->build()
        );
        $actual = $this->containerDefinitionSerializer->serialize($containerDefinition);

        $this->assertJsonStringEqualsJsonString($serialized, $actual);
    }

    public function testFailingToWriteCacheFileThrowsException() {
        $dir = Fixtures::implicitAliasedServices()->getPath();
        $subject = new CacheAwareContainerDefinitionCompiler(
            $this->phpParserContainerDefinitionCompiler = new AnnotatedTargetContainerDefinitionCompiler(
                new PhpParserAnnotatedTargetParser(),
                new DefaultAnnotatedTargetDefinitionConverter()
            ),
            $this->containerDefinitionSerializer = new JsonContainerDefinitionSerializer(),
            'vfs://cache'
        );


        $this->expectException(InvalidCacheException::class);
        $this->expectExceptionMessage('The cache directory, vfs://cache, could not be written to. Please ensure it exists and is writeable.');

        $subject->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories($dir)->build());
    }


}