<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\Exception\InvalidCacheException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class CacheAwareContainerDefinitionCompilerTest extends TestCase {

    private CacheAwareContainerDefinitionCompiler $cacheAwareContainerDefinitionCompiler;
    private PhpParserContainerDefinitionCompiler $phpParserContainerDefinitionCompiler;
    private ContainerDefinitionSerializer $containerDefinitionSerializer;
    private vfsStreamDirectory $root;

    protected function setUp(): void {
        $this->cacheAwareContainerDefinitionCompiler = new CacheAwareContainerDefinitionCompiler(
            $this->phpParserContainerDefinitionCompiler = new PhpParserContainerDefinitionCompiler(),
            $this->containerDefinitionSerializer = new JsonContainerDefinitionSerializer(),
            'vfs://root'
        );
        $this->root = vfsStream::setup();
    }

    public function testFileDoesNotExistWritesFile() {
        $dir = DummyAppUtils::getRootDir() . '/SimpleServices';
        $containerDefinition = $this->cacheAwareContainerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories($dir)
                ->withProfiles('test')
                ->build()
        );

        $this->assertNotNull($this->root->getChild('root/' . md5('test' . $dir)));

        $expected = $this->containerDefinitionSerializer->serialize($containerDefinition);
        $actual = $this->root->getChild('root/' . md5('test' . $dir))->getContent();

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testFileDoesExistDoesNotCallCompiler() {
        $dir = DummyAppUtils::getRootDir() . '/ProfileResolvedServices';
        $containerDefinition = $this->phpParserContainerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories($dir)->withProfiles('default')->build()
        );
        $serialized = $this->containerDefinitionSerializer->serialize($containerDefinition);

        vfsStream::newFile(md5('test' . $dir))->at($this->root)->setContent($serialized);

        $mock = $this->getMockBuilder(ContainerDefinitionCompiler::class)->getMock();
        $mock->expects($this->never())->method('compile');
        $subject = new CacheAwareContainerDefinitionCompiler(
            $mock,
            $this->containerDefinitionSerializer,
            'vfs://root'
        );

        $containerDefinition = $subject->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories($dir)->withProfiles('test')->build()
        );
        $actual = $this->containerDefinitionSerializer->serialize($containerDefinition);

        $this->assertJsonStringEqualsJsonString($serialized, $actual);
    }

    public function testFailingToWriteCacheFileThrowsException() {
        $dir = DummyAppUtils::getRootDir() . '/ProfileResolvedServices';
        $subject = new CacheAwareContainerDefinitionCompiler(
            $this->phpParserContainerDefinitionCompiler = new PhpParserContainerDefinitionCompiler(),
            $this->containerDefinitionSerializer = new JsonContainerDefinitionSerializer(),
            'vfs://cache'
        );


        $this->expectException(InvalidCacheException::class);
        $this->expectExceptionMessage('The cache directory, vfs://cache, could not be written to. Please ensure it exists and is writeable.');

        $subject->compile(ContainerDefinitionCompileOptionsBuilder::scanDirectories($dir)->withProfiles('default')->build());
    }


}