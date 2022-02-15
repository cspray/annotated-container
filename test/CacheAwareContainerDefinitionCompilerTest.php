<?php

namespace Cspray\AnnotatedContainer;

use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;
use Vfs\Node\Directory;
use Vfs\Node\File;

class CacheAwareContainerDefinitionCompilerTest extends TestCase {

    private CacheAwareContainerDefinitionCompiler $cacheAwareContainerDefinitionCompiler;
    private PhpParserContainerDefinitionCompiler $phpParserContainerDefinitionCompiler;
    private ContainerDefinitionSerializer $containerDefinitionSerializer;
    private FileSystem $fileSystem;

    protected function setUp(): void {
        $this->cacheAwareContainerDefinitionCompiler = new CacheAwareContainerDefinitionCompiler(
            $this->phpParserContainerDefinitionCompiler = new PhpParserContainerDefinitionCompiler(),
            $this->containerDefinitionSerializer = new JsonContainerDefinitionSerializer(),
            'vfs://cache'
        );
        $this->fileSystem = FileSystem::factory('vfs://');
        $this->fileSystem->mount();
        $this->fileSystem->get('/')->add('cache', new Directory());
    }

    protected function tearDown(): void {
        $this->fileSystem->unmount();
    }

    public function testFileDoesNotExistWritesFile() {
        $dir = __DIR__ . '/DummyApps/SimpleServices';
        $containerDefinition = $this->cacheAwareContainerDefinitionCompiler->compileDirectory('test', [$dir]);

        $this->assertNotNull($this->fileSystem->get('/cache/' . md5('test' . $dir)));

        $expected = $this->containerDefinitionSerializer->serialize($containerDefinition);
        $actual = $this->fileSystem->get('/cache/' . md5('test' . $dir))->getContent();

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testFileDoesExistDoesNotCallCompiler() {
        $dir = __DIR__ . '/DummyApps/EnvironmentResolvedServices';
        $containerDefinition = $this->phpParserContainerDefinitionCompiler->compileDirectory('test', $dir);
        $serialized = $this->containerDefinitionSerializer->serialize($containerDefinition);

        $this->fileSystem->get('/cache')->add(md5('test' . $dir), new File($serialized));

        $mock = $this->getMockBuilder(ContainerDefinitionCompiler::class)->getMock();
        $mock->expects($this->never())->method('compileDirectory');
        $subject = new CacheAwareContainerDefinitionCompiler(
            $mock,
            $this->containerDefinitionSerializer,
            'vfs://cache'
        );

        $containerDefinition = $subject->compileDirectory('test', $dir);
        $actual = $this->containerDefinitionSerializer->serialize($containerDefinition);

        $this->assertJsonStringEqualsJsonString($serialized, $actual);
    }

    public function testFailingToWriteCacheFileThrowsException() {
        $dir = __DIR__ . '/DummyApps/EnvironmentResolvedServices';
        $this->fileSystem->get('/')->remove('cache');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The cache directory, vfs://cache, could not be written to. Please ensure it exists and is writeable.');

        $this->cacheAwareContainerDefinitionCompiler->compileDirectory('test', $dir);
    }


}