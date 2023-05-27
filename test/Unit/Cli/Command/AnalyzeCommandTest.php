<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Cli\Command;

use Cspray\AnnotatedContainer\Cli\Command\AnalyzeCommand;
use Cspray\AnnotatedContainer\Cli\Exception\ConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\TerminalOutput;
use Cspray\AnnotatedContainer\Unit\Helper\FixtureBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Unit\Helper\InMemoryOutput;
use Cspray\AnnotatedContainer\Unit\Helper\StubInput;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\LogicalConstraintFixtures;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;

final class AnalyzeCommandTest extends TestCase {

    private VirtualDirectory $vfs;

    private AnalyzeCommand $subject;

    private InMemoryOutput $stdout;

    private InMemoryOutput $stderr;
    private TerminalOutput $output;

    protected function setUp() : void {
        $this->vfs = VirtualFilesystem::setup();
        $this->subject = new AnalyzeCommand(
            new FixtureBootstrappingDirectoryResolver()
        );

        $this->stdout = new InMemoryOutput();
        $this->stderr = new InMemoryOutput();
        $this->output = new TerminalOutput($this->stdout, $this->stderr);
    }

    public function testGetCommandName() : void {
        $actual = $this->subject->getName();

        self::assertSame('analyze', $actual);
    }

    public function testGetCommandHelp() : void {
        $expected = <<<TEXT
NAME

    analyze - Ensure container definition validates against all logical constraints.
    
SYNOPSIS

    <bold>analyze</bold> [OPTION]...

DESCRIPTION

    <bold>analyze</bold> will analyze your codebase and create a ContainerDefinition. 
    All logical constraints will be run and results will be output to the terminal.

OPTIONS

    --config-file="file-path.xml"

        Set the name of the configuration file to be used. If not provided the
        default value will be "annotated-container.xml".

TEXT;

        self::assertSame($expected, $this->subject->getHelp());
    }

    public function testHandleWithNoConfigurationFilePresentHasTerminalError() : void {
        $input = new StubInput([], []);

        $this->expectException(ConfigurationNotFound::class);
        $this->expectExceptionMessage('No configuration file found at "vfs://root/annotated-container.xml".');

        $this->subject->handle($input, $this->output);
    }

    public function testHandleWithConfigurationFilePresentShowsNoLogicalConstraints() : void {
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

        $expected = <<<TEXT
Annotated Container Analysis

Configuration file: vfs://root/annotated-container.xml
Logical Constraints:

- Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceName
- Cspray\AnnotatedContainer\LogicalConstraint\Check\NonPublicServiceDelegate

\033[32mNo logical constraint violations were found!\033[0m

TEXT;

        $this->subject->handle(new StubInput([], []), $this->output);

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testHandleWithConfigurationFilePresentShowsLogicalConstraints() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>LogicalConstraints/DuplicateServiceName</dir>
    </source>
  </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($config)
            ->at($this->vfs);

        $banner = str_repeat('*', 80);
        $barService = LogicalConstraintFixtures::duplicateServiceName()->getBarService()->getName();
        $fooService = LogicalConstraintFixtures::duplicateServiceName()->getFooService()->getName();
        $expected = <<<TEXT
Annotated Container Analysis

Configuration file: vfs://root/annotated-container.xml
Logical Constraints:

- Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceName
- Cspray\AnnotatedContainer\LogicalConstraint\Check\NonPublicServiceDelegate

Violation #1 - \033[31mCritical\033[0m
$banner

There are multiple services with the name "foo". The service types are:

- $barService
- $fooService

\033[1m\033[31mERROR!\033[0m\033[22m Total violations found: \033[1m1\033[22m

TEXT;

        $this->subject->handle(new StubInput([], []), $this->output);

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

}
