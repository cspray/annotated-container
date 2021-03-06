<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\Cli\Exception\CacheDirConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\ConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\InvalidBuild;
use Cspray\AnnotatedContainer\Cli\Exception\InvalidOptionType;
use Cspray\AnnotatedContainer\Cli\TerminalOutput;
use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\Helper\FixtureBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Helper\InMemoryOutput;
use Cspray\AnnotatedContainer\Helper\StubInput;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use function Cspray\AnnotatedContainer\compiler;

class BuildCommandTest extends TestCase {

    private BuildCommand $subject;

    private InMemoryOutput $stdout;
    private InMemoryOutput $stderr;
    private TerminalOutput $output;

    private VirtualDirectory $vfs;

    protected function setUp() : void {
        parent::setUp();
        $this->subject = new BuildCommand(
            new FixtureBootstrappingDirectoryResolver()
        );

        $this->stdout = new InMemoryOutput();
        $this->stderr = new InMemoryOutput();
        $this->output = new TerminalOutput($this->stdout, $this->stderr);

        $this->vfs = VirtualFilesystem::setup();
    }

    public function testGetName() : void {
        self::assertSame('build', $this->subject->getName());
    }

    public function testGetHelp() : void {
        $expected = <<<SHELL
NAME

    build - Compile a ContainerDefinition and cache it according to the defined configuration file.
    
SYNOPSIS

    <bold>build</bold> [OPTION]...

DESCRIPTION

    <bold>build</bold> will compile and cache a ContainerDefinition based on the 
    configuration file present. If your configuration has disabled caching running
    this command will result in an error.

OPTIONS

    --config-file="file-path.xml"

        Set the name of the configuration file to be used. If not provided the
        default value will be "annotated-container.xml".

SHELL;

        self::assertSame($expected, $this->subject->getHelp());
    }

    public function testConfigurationFileNotPresent() : void {
        self::expectException(ConfigurationNotFound::class);
        self::expectExceptionMessage(
            'No configuration file found at "annotated-container.xml".'
        );

        $input = new StubInput([], ['build']);
        $this->subject->handle($input, $this->output);
    }

    public function testConfigurationFilePresentCacheContainerDefinition() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>SingleConcreteService</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($config)
            ->at($this->vfs);

        VirtualFilesystem::newDirectory('.annotated-container-cache')->at($this->vfs);

        $input = new StubInput([], ['build']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
        $expectedKey = md5(Fixtures::singleConcreteService()->getPath());
        self::assertFileExists('vfs://root/.annotated-container-cache/' . $expectedKey);
    }

    public function testConfigurationFileDoesNotHaveCacheDirectory() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>SingleConcreteService</dir>
    </source>
  </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($config)
            ->at($this->vfs);

        self::expectException(CacheDirConfigurationNotFound::class);
        self::expectExceptionMessage('Building a Container without a configured cache directory is not supported.');

        $input = new StubInput([], ['build']);
        $this->subject->handle($input, $this->output);
    }

    public function testBuildCommandRespectsConfigFileOptionPassed() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>AmbiguousAliasedServices</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('my-config.xml')
            ->withContent($config)
            ->at($this->vfs);

        VirtualFilesystem::newDirectory('.annotated-container-cache')->at($this->vfs);

        $input = new StubInput(['config-file' => 'my-config.xml'], ['build']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
        $expectedKey = md5(Fixtures::ambiguousAliasedServices()->getPath());
        self::assertFileExists('vfs://root/.annotated-container-cache/' . $expectedKey);
    }

    public function testBuildCommandRespectsConfigFileFromComposerJson() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>AmbiguousAliasedServices</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('composer.json')
            ->withContent(json_encode([
                'extra' => [
                    'annotatedContainer' => [
                        'configFile' => 'composer-config.xml'
                    ]
                ]
            ]))->at($this->vfs);

        VirtualFilesystem::newFile('composer-config.xml')
            ->withContent($config)
            ->at($this->vfs);

        VirtualFilesystem::newDirectory('.annotated-container-cache')->at($this->vfs);

        $input = new StubInput([], ['build']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
        $expectedKey = md5(Fixtures::ambiguousAliasedServices()->getPath());
        self::assertFileExists('vfs://root/.annotated-container-cache/' . $expectedKey);
    }

    public function testBuildCommandConfigFileBooleansThrowsException() : void {
        self::expectException(InvalidOptionType::class);
        self::expectExceptionMessage('The option "config-file" MUST NOT be a flag-only option.');

        $input = new StubInput(['config-file' => true], ['build']);
        $this->subject->handle($input, $this->output);
    }

    public function testBuildCommandConfigFileArrayThrowsException() : void {
        self::expectException(InvalidOptionType::class);
        self::expectExceptionMessage('The option "config-file" MUST NOT be provided multiple times.');

        $input = new StubInput(['config-file' => ['a', 'b']], ['build']);
        $this->subject->handle($input, $this->output);
    }

    public function testBuildCommandCachesContainerDefinitionWithConsumers() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>ThirdPartyServices</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
  <containerDefinitionBuilderContextConsumer>
    Cspray\AnnotatedContainer\Helper\StubContextConsumer
  </containerDefinitionBuilderContextConsumer>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($config)
            ->at($this->vfs);

        VirtualFilesystem::newDirectory('.annotated-container-cache')->at($this->vfs);

        $input = new StubInput([], ['build']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
        $expectedKey = md5(Fixtures::thirdPartyServices()->getPath());
        self::assertFileExists('vfs://root/.annotated-container-cache/' . $expectedKey);

        $containerDefinition = compiler('vfs://root/.annotated-container-cache')->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(Fixtures::thirdPartyServices()->getPath())->build()
        );

        self::assertCount(2, $containerDefinition->getServiceDefinitions());
        self::assertSame(
            Fixtures::thirdPartyServices()->fooInterface(),
            $containerDefinition->getServiceDefinitions()[0]->getType()
        );
        self::assertSame(
            Fixtures::thirdPartyServices()->fooImplementation(),
            $containerDefinition->getServiceDefinitions()[1]->getType()
        );
    }

    public function testSuccessfulBuildHasCorrectOutput() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>SingleConcreteService</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($config)
            ->at($this->vfs);

        VirtualFilesystem::newDirectory('.annotated-container-cache')->at($this->vfs);

        $input = new StubInput([], ['build']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
        $expected = <<<SHELL
\033[32mSuccessfully built and cached your Container!\033[0m

SHELL;

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

}