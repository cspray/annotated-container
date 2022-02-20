<?php

namespace Cspray\AnnotatedContainer\Console;

use Cspray\AnnotatedContainer\JsonContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\PhpParserContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\DummyApps\SimpleServices;
use Cspray\AnnotatedContainer\DummyApps\ProfileResolvedServices;
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
        $envOption = $command->getDefinition()->getOption('profiles');

        $this->assertNull($envOption->getShortcut());
        $this->assertTrue($envOption->isValueRequired());
        $this->assertFalse($envOption->isNegatable());
        $this->assertTrue($envOption->isArray());
        $this->assertSame('The profiles to use when compiling a ContainerDefinition.', $envOption->getDescription());
        $this->assertSame(['default'], $envOption->getDefault());
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

    private function executeSuccessTester(array $args) : array {
        $command = $this->application->find('compile');
        $tester = new CommandTester($command);

        $tester->execute($args, ['capture_stderr_separately' => true]);
        $tester->assertCommandIsSuccessful();

        $this->assertNotEmpty($tester->getDisplay());
        return json_decode($tester->getDisplay(), true);
    }

    public function testCompileContainerCommandSimpleServicesHasCompiledServiceDefinitions() {
        $actual = $this->executeSuccessTester(['dirs' => ['SimpleServices']]);
        $this->assertArrayHasKey('compiledServiceDefinitions', $actual);
        $this->assertCount(2, $actual['compiledServiceDefinitions']);

        $this->assertArrayHasKey(md5(SimpleServices\FooInterface::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals([
            'type' => SimpleServices\FooInterface::class,
            'implementedServices' => [],
            'profiles' => [
                'default'
            ],
            'isAbstract' => true,
            'isConcrete' => false
        ], $actual['compiledServiceDefinitions'][md5(SimpleServices\FooInterface::class)]);

        $this->assertArrayHasKey(md5(SimpleServices\FooImplementation::class), $actual['compiledServiceDefinitions']);
        $this->assertEquals([
            'type' => SimpleServices\FooImplementation::class,
            'implementedServices' => [md5(SimpleServices\FooInterface::class)],
            'profiles' => [
                'default'
            ],
            'isAbstract' => false,
            'isConcrete' => true
        ], $actual['compiledServiceDefinitions'][md5(SimpleServices\FooImplementation::class)]);
    }

    public function testCompileContainerCommandSimpleServicesHasSharedServiceDefinitions() {
        $actual = $this->executeSuccessTester(['dirs' => ['SimpleServices']]);

        $this->assertArrayHasKey('sharedServiceDefinitions', $actual);
        $this->assertContains(md5(SimpleServices\FooInterface::class), $actual['sharedServiceDefinitions']);
        $this->assertContains(md5(SimpleServices\FooImplementation::class), $actual['sharedServiceDefinitions']);
    }

    public function testCompileContainerCommandSimpleServicesHasAliasDefinitions() {
        $actual = $this->executeSuccessTester(['dirs' => ['SimpleServices']]);

        $this->assertArrayHasKey('aliasDefinitions', $actual);
        $this->assertCount(1, $actual['aliasDefinitions']);
        $this->assertEquals([
            'original' => md5(SimpleServices\FooInterface::class),
            'alias' => md5(SimpleServices\FooImplementation::class)
        ], $actual['aliasDefinitions'][0]);
    }

    public function testCompileContainerCommandSimpleServicesHasNoServicePrepareDefinitions() {
        $actual = $this->executeSuccessTester(['dirs' => ['SimpleServices']]);

        $this->assertArrayHasKey('servicePrepareDefinitions', $actual);
        $this->assertEmpty($actual['servicePrepareDefinitions']);
    }

    public function testCompileContainerCommandSimpleServicesHasNoInjectServiceDefinitions() {
        $actual = $this->executeSuccessTester(['dirs' => ['SimpleServices']]);

        $this->assertArrayHasKey('injectServiceDefinitions', $actual);
        $this->assertEmpty($actual['injectServiceDefinitions']);
    }

    public function testCompileContainerCommandRespectsEnvironmentFlag() {
        $command = $this->application->find('compile');
        $tester = new CommandTester($command);

        $tester->execute([
            'dirs' => ['ProfileResolvedServices'],
            '--profiles' => ['dev']
        ], ['capture_stderr_separately' => true]);
        $tester->assertCommandIsSuccessful();

        $this->assertNotEmpty($tester->getDisplay());
        $actual = json_decode($tester->getDisplay(), true);

        $this->assertArrayHasKey('compiledServiceDefinitions', $actual);
        $this->assertCount(2, $actual['compiledServiceDefinitions']);
        $this->assertArrayHasKey(md5(ProfileResolvedServices\FooInterface::class), $actual['compiledServiceDefinitions']);
        $this->assertArrayHasKey(md5(ProfileResolvedServices\DevFooImplementation::class), $actual['compiledServiceDefinitions']);
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
        $this->assertSame('The compiled ContainerDefinition was written to vfs://root/' . md5('defaultNonPhpFiles'), trim($tester->getDisplay()));
        $this->assertSame($this->getExpectedNonPhpFilesPrettyPrint(), $this->root->getChild(md5('defaultNonPhpFiles'))->getContent());
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
            "profiles": [
                "default"
            ],
            "isAbstract": true,
            "isConcrete": false
        }
    },
    "sharedServiceDefinitions": [
        "$serviceKey"
    ],
    "aliasDefinitions": [],
    "servicePrepareDefinitions": [],
    "injectScalarDefinitions": [],
    "injectServiceDefinitions": [],
    "serviceDelegateDefinitions": []
}
JSON;
    }

}