<?php

namespace Cspray\AnnotatedContainer\Console;

use Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\ContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\ContainerDefinitionSerializerOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CompileContainerCommand extends Command {

    private ContainerDefinitionCompiler $containerDefinitionCompiler;
    private ContainerDefinitionSerializer $containerDefinitionSerializer;
    private string $rootDir;

    public function __construct(
        ContainerDefinitionCompiler $containerDefinitionCompiler,
        ContainerDefinitionSerializer $containerDefinitionSerializer,
        string $rootDir
    ) {
        parent::__construct();
        $this->containerDefinitionCompiler = $containerDefinitionCompiler;
        $this->containerDefinitionSerializer = $containerDefinitionSerializer;
        $this->rootDir = $rootDir;
    }

    protected function configure() : void {
        $this->setName('compile')
            ->addOption(
                name: 'profiles',
                mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                description: 'The profiles to use when compiling a ContainerDefinition.',
                default: ['default']
            )
            ->addOption(
                name: 'cache-dir',
                shortcut: 'c',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The directory where the ContainerDefinition should be cached to. If this option is not present the serialized ContainerDefinition will be sent to stdout.'
            )->addOption(
                name: 'pretty-print',
                mode: InputOption::VALUE_NONE | InputOption::VALUE_NEGATABLE,
                description: 'Determine whether to output the JSON in a human-readable format.'
            )->addArgument(
                name: 'dirs',
                mode: InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                description: 'A list of directories to scan for Attributes.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $directories = $input->getArgument('dirs');
        $goodDirs = [];
        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                $goodDirs[] = $directory;
            } else {
                $dir = sprintf("%s/%s", $this->rootDir, $directory);
                if (!is_dir($dir)) {
                    $errOutput->writeln(sprintf('<error>The directory provided, "%s", could not be read from.</error>', $directory));
                    return Command::FAILURE;
                } else {
                    $goodDirs[] = $dir;
                }
            }
        }

        $profiles = $input->getOption('profiles');
        $injectorDefinition = $this->containerDefinitionCompiler->compile(
            ContainerDefinitionCompileOptionsBuilder::scanDirectories(...$goodDirs)
                ->withProfiles(...$profiles)
                ->build()
        );
        $serializerOptions = new ContainerDefinitionSerializerOptions();
        if ($input->getOption('pretty-print')) {
            $serializerOptions = $serializerOptions->withPrettyFormatting();
        }
        $json = $this->containerDefinitionSerializer->serialize($injectorDefinition, $serializerOptions);

        $cacheDir = $input->getOption('cache-dir');
        if (!isset($cacheDir)) {
            $output->writeln($json);
        } else {
            $outputTarget = sprintf('%s/%s', $cacheDir, md5(join($profiles) . join($directories)));
            $contentWritten = @file_put_contents($outputTarget, $json);
            // intentionally checking for 0 bytes written... if we didn't write anything that's still an error
            if (!$contentWritten) {
                $errOutput->writeln(sprintf('<error>The cache directory, %s, could not be written to.</error>', $cacheDir));
                return Command::FAILURE;
            }
            $output->writeln(sprintf('The compiled ContainerDefinition was written to %s', $outputTarget));
        }

        return Command::SUCCESS;
    }

}