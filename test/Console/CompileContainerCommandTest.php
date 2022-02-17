<?php

namespace Cspray\AnnotatedContainer\Console;

use Cspray\AnnotatedContainer\JsonContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\PhpParserContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\DummyApps\SimpleServices;
use Cspray\AnnotatedContainer\DummyApps\EnvironmentResolvedServices;
use Cspray\AnnotatedContainer\DummyApps\NonPhpFiles;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CompileContainerCommandTest extends TestCase {

    private Application $application;
    private vfsStreamDirectory $root;

    protected function setUp(): void {
        $this->application = new Application('annotated-container-test', '0.1.0');
        $this->application->add(new CompileContainerCommand(
            new PhpParserContainerDefinitionCompiler(),
            new JsonContainerDefinitionSerializer(),
            dirname(__DIR__) . '/DummyApps'
        ));
        $this->root = vfsStream::setup();
    }


    public function testCompileContainerCommandName() {
        $command = $this->application->find('compile');
        $this->assertNotNull($command);
    }

    public function testCompileContainerCommandHelp() {
        $command = $this->application->find('compile');
        $this->assertSame('', $command->getHelp());
    }

    public function testCompileContainerCommandOutputFlag() {
        $command = $this->application->find('compile');
        $outputOption = $command->getDefinition()->getOption('cache-dir');

        $this->assertSame('c', $outputOption->getShortcut());
        $this->assertTrue($outputOption->isValueRequired());
        $this->assertFalse($outputOption->isNegatable());
        $this->assertFalse($outputOption->isArray());
        $this->assertSame('The directory where the ContainerDefinition should be cached to. If this option is not present the serialized ContainerDefinition will be sent to stdout.', $outputOption->getDescription());
        $this->assertSame(null, $outputOption->getDefault());
    }

    public function testCompileContainerCommandPrettyPrintFlag() {
        $command = $this->application->find('compile');
        $prettyPrintOption = $command->getDefinition()->getOption('pretty-print');

        $this->assertNull($prettyPrintOption->getShortcut());
        $this->assertFalse($prettyPrintOption->acceptValue());
        $this->assertTrue($prettyPrintOption->isNegatable());
        $this->assertFalse($prettyPrintOption->isArray());
        $this->assertSame('Determine whether to output the JSON in a human-readable format.', $prettyPrintOption->getDescription());
    }

    public function testCompileContainerCommandEnvironmentFlag() {
        $command = $this->application->find('compile');
        $envOption = $command->getDefinition()->getOption('env');

        $this->assertSame('e', $envOption->getShortcut());
        $this->assertTrue($envOption->isValueRequired());
        $this->assertFalse($envOption->isNegatable());
        $this->assertFalse($envOption->isArray());
        $this->assertSame('The environment to use when compiling a ContainerDefinition.', $envOption->getDescription());
        $this->assertSame('dev', $envOption->getDefault());
    }

    public function testCompileContainerCommandDirArguments() {
        $command = $this->application->find('compile');
        $dirsArgument = $command->getDefinition()->getArgument('dirs');

        $this->assertTrue($dirsArgument->isRequired());
        $this->assertTrue($dirsArgument->isArray());
        $this->assertSame('A list of directories to scan for Attributes.', $dirsArgument->getDescription());
    }

    public function testCompileContainerCommandDirectoryArgumentIsNotADirectory() {
        $command = $this->application->find('compile');
        $tester = new CommandTester($command);

        $tester->execute(['dirs' => ['not a directory']], ['capture_stderr_separately' => true]);
        $this->assertSame(1, $tester->getStatusCode());
        $this->assertEmpty($tester->getDisplay());
        $this->assertSame('The directory provided, "not a directory", could not be read from.' . PHP_EOL, $tester->getErrorOutput());
    }

    public function testCompileContainerCommandDirectoryArgumentIsSubDirectoryOfRoot() {
        $command = $this->application->find('compile');
        $tester = new CommandTester($command);

        $tester->execute(['dirs' => ['SimpleServices']], ['capture_stderr_separately' => true]);
        $tester->assertCommandIsSuccessful();

        $this->assertNotEmpty($tester->getDisplay());
        $actual = json_decode($tester->getDisplay(), true);
        $expected = [
            'compiledServiceDefinitions' => [
                md5(SimpleServices\FooInterface::class) => [
                    'type' => SimpleServices\FooInterface::class,
                    'implementedServices' => [],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => true,
                    'isClass' => false,
                    'isAbstract' => false
                ],
                md5(SimpleServices\FooImplementation::class) => [
                    'type' => SimpleServices\FooImplementation::class,
                    'implementedServices' => [md5(SimpleServices\FooInterface::class)],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ]
            ],
            'sharedServiceDefinitions' => [
                md5(SimpleServices\FooImplementation::class),
                md5(SimpleServices\FooInterface::class)
            ],
            'aliasDefinitions' => [
                [
                    'original' => md5(SimpleServices\FooInterface::class),
                    'alias' => md5(SimpleServices\FooImplementation::class)
                ]
            ],
            'servicePrepareDefinitions' => [],
            'useScalarDefinitions' => [],
            'useServiceDefinitions' => [],
            'serviceDelegateDefinitions' => []
        ];
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testCompileContainerCommandRespectsEnvironmentFlag() {
        $command = $this->application->find('compile');
        $tester = new CommandTester($command);

        $tester->execute(['dirs' => ['EnvironmentResolvedServices']], ['capture_stderr_separately' => true]);
        $tester->assertCommandIsSuccessful();

        $this->assertNotEmpty($tester->getDisplay());
        $actual = json_decode($tester->getDisplay(), true);
        $expected = [
            'compiledServiceDefinitions' => [
                md5(EnvironmentResolvedServices\FooInterface::class) => [
                    'type' => EnvironmentResolvedServices\FooInterface::class,
                    'implementedServices' => [],
                    'extendedServices' => [],
                    'environments' => [],
                    'isInterface' => true,
                    'isClass' => false,
                    'isAbstract' => false
                ],
                md5(EnvironmentResolvedServices\DevFooImplementation::class) => [
                    'type' => EnvironmentResolvedServices\DevFooImplementation::class,
                    'implementedServices' => [md5(EnvironmentResolvedServices\FooInterface::class)],
                    'extendedServices' => [],
                    'environments' => ['dev'],
                    'isInterface' => false,
                    'isClass' => true,
                    'isAbstract' => false
                ]
            ],
            'sharedServiceDefinitions' => [
                md5(EnvironmentResolvedServices\FooInterface::class),
                md5(EnvironmentResolvedServices\DevFooImplementation::class)
            ],
            'aliasDefinitions' => [
                [
                    'original' => md5(EnvironmentResolvedServices\FooInterface::class),
                    'alias' => md5(EnvironmentResolvedServices\DevFooImplementation::class)
                ]
            ],
            'servicePrepareDefinitions' => [],
            'useScalarDefinitions' => [],
            'useServiceDefinitions' => [],
            'serviceDelegateDefinitions' => []
        ];
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testCompileContainerCommandRespectsPrettyPrintFlag() {
        $command = $this->application->find('compile');
        $tester = new CommandTester($command);

        $tester->execute([
            'dirs' => ['NonPhpFiles'],
            '--pretty-print' => true,
        ], ['capture_stderr_separately' => true]);
        $tester->assertCommandIsSuccessful();
        $this->assertNotEmpty($tester->getDisplay());
        $this->assertSame($this->getExpectedNonPhpFilesPrettyPrint(), trim($tester->getDisplay()));
    }

    public function testCacheDirFlagRespected() {
        $command = $this->application->find('compile');
        $tester = new CommandTester($command);

        $tester->execute([
            'dirs' => ['NonPhpFiles'],
            '--pretty-print' => true,
            '--cache-dir' => vfsStream::url('root')
        ], ['capture_stderr_separately' => true]);

        $tester->assertCommandIsSuccessful();
        $this->assertNotEmpty($tester->getDisplay());
        $this->assertSame('The compiled ContainerDefinition was written to vfs://root/' . md5('devNonPhpFiles'), trim($tester->getDisplay()));
        $this->assertSame($this->getExpectedNonPhpFilesPrettyPrint(), $this->root->getChild(md5('devNonPhpFiles'))->getContent());
    }

    public function testOutputFlagNotWritableThrowsError() {
        $command = $this->application->find('compile');
        $tester = new CommandTester($command);

        $tester->execute([
            'dirs' => ['SimpleServices'],
            '--cache-dir' => 'vfs://no-dir'
        ], ['capture_stderr_separately' => true]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertEmpty($tester->getDisplay());
        $this->assertSame('The cache directory, vfs://no-dir, could not be written to.', trim($tester->getErrorOutput()));
    }

    public function testSourceDirectoriesIncludeFullPath() {
        $command = $this->application->find('compile');
        $tester = new CommandTester($command);

        $tester->execute([
            'dirs' => [dirname(__DIR__) . '/DummyApps/NonPhpFiles'],
            '--pretty-print' => true
        ], ['capture_stderr_separately' => true]);

        $tester->assertCommandIsSuccessful();
        $this->assertSame($this->getExpectedNonPhpFilesPrettyPrint(), trim($tester->getDisplay()));
    }

    private function getExpectedNonPhpFilesPrettyPrint() : string {
        $serviceKey = md5(NonPhpFiles\FooInterface::class);
        return <<<JSON
{
    "compiledServiceDefinitions": {
        "$serviceKey": {
            "type": "Cspray\\\\AnnotatedContainer\\\\DummyApps\\\\NonPhpFiles\\\\FooInterface",
            "implementedServices": [],
            "extendedServices": [],
            "environments": [],
            "isInterface": true,
            "isClass": false,
            "isAbstract": false
        }
    },
    "sharedServiceDefinitions": [
        "$serviceKey"
    ],
    "aliasDefinitions": [],
    "servicePrepareDefinitions": [],
    "useScalarDefinitions": [],
    "useServiceDefinitions": [],
    "serviceDelegateDefinitions": []
}
JSON;
    }

}