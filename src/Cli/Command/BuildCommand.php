<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\XmlBootstrappingConfiguration;
use Cspray\AnnotatedContainer\Cli\Command;
use Cspray\AnnotatedContainer\Cli\Exception\CacheDirConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\ConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\InvalidOptionType;
use Cspray\AnnotatedContainer\Cli\Input;
use Cspray\AnnotatedContainer\Cli\TerminalOutput;
use Cspray\AnnotatedContainer\Compile\AnnotatedTargetContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\Compile\CacheAwareContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\Compile\DefaultAnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;

final class BuildCommand implements Command {

    public function __construct(
        private readonly BootstrappingDirectoryResolver $directoryResolver
    ) {}

    public function getName() : string {
        return 'build';
    }

    public function getHelp() : string {
        return <<<SHELL
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
    }

    public function handle(Input $input, TerminalOutput $output) : int {
        $configName = $input->getOption('config-file');
        if (!isset($configName)) {
            // This not being present would be highly irregular and not party of the happy path
            // But it is possible that somebody created the configuration manually and is not using composer
            $composerFile = $this->directoryResolver->getConfigurationPath('composer.json');
            if (file_exists($composerFile)) {
                /** @var mixed $composer */
                $composer = json_decode(file_get_contents($composerFile), true);
                assert(is_array($composer));
                $configName = $composer['extra']['annotatedContainer']['configFile'] ?? 'annotated-container.xml';
            } else {
                $configName = 'annotated-container.xml';
            }
        } else {
            if (is_bool($configName)) {
                throw InvalidOptionType::fromBooleanOption('config-file');
            } else if (is_array($configName)) {
                throw InvalidOptionType::fromArrayOption('config-file');
            }
        }

        assert(is_string($configName));
        $configFile = $this->directoryResolver->getConfigurationPath($configName);
        if (!file_exists($configFile)) {
            throw ConfigurationNotFound::fromMissingFile($configName);
        }

        $config = new XmlBootstrappingConfiguration($configFile, $this->directoryResolver);

        $cacheDir = $config->getCacheDirectory();
        if (!isset($cacheDir)) {
            throw CacheDirConfigurationNotFound::fromBuildCommand();
        }

        $cacheDir = $this->directoryResolver->getCachePath($cacheDir);
        $scanDirs = [];
        foreach ($config->getScanDirectories() as $scanDirectory) {
            $scanDirs[] = $this->directoryResolver->getPathFromRoot($scanDirectory);
        }

        $compileOptions = ContainerDefinitionCompileOptionsBuilder::scanDirectories(...$scanDirs);
        $containerDefinitionConsumer = $config->getContainerDefinitionProvider();
        if ($containerDefinitionConsumer !== null) {
            $compileOptions = $compileOptions->withDefinitionProvider($containerDefinitionConsumer);
        }

        $logger = $config->getLogger();
        if ($logger !== null) {
            $compileOptions = $compileOptions->withLogger($logger);
        }

        $this->getCompiler($cacheDir)->compile($compileOptions->build());

        $output->stdout->write('<fg:green>Successfully built and cached your Container!</fg:green>');

        return 0;
    }

    private function getCompiler(?string $cacheDir) : ContainerDefinitionCompiler {
        $compiler = new AnnotatedTargetContainerDefinitionCompiler(
            new PhpParserAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
        if ($cacheDir !== null) {
            $compiler = new CacheAwareContainerDefinitionCompiler($compiler, new ContainerDefinitionSerializer(), $cacheDir);
        }

        return $compiler;
    }
}