<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameterSet;
use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\PostAnalysisObserver;
use Cspray\AnnotatedContainer\Cli\Command;
use Cspray\AnnotatedContainer\Cli\Exception\ConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Input;
use Cspray\AnnotatedContainer\Cli\TerminalOutput;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptions;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Exception\UnsupportedOperation;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceName;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\NonPublicServiceDelegate;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\NonPublicServicePrepare;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintValidator;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

final class AnalyzeCommand implements Command {

    public function __construct(
        private readonly BootstrappingDirectoryResolver $directoryResolver
    ) {}

    public function getName() : string {
        return 'analyze';
    }

    public function getHelp() : string {
        return <<<TEXT
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
    }

    public function handle(Input $input, TerminalOutput $output) : int {
        $configFile = $this->directoryResolver->getConfigurationPath('annotated-container.xml');
        if (!is_file($configFile)) {
            throw ConfigurationNotFound::fromMissingFile($this->directoryResolver->getConfigurationPath('annotated-container.xml'));
        }

        $infoCapturingObserver = new class implements PostAnalysisObserver {
            public ?ActiveProfiles $activeProfiles = null;

            public ?ContainerDefinition $containerDefinition = null;

            public function notifyPostAnalysis(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void {
                $this->activeProfiles = $activeProfiles;
                $this->containerDefinition = $containerDefinition;
            }
        };

        $noOpContainerFactory = new class implements ContainerFactory {
            public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : AnnotatedContainer {
                return new class implements AnnotatedContainer {

                    public function getBackingContainer() : object {
                        throw UnsupportedOperation::fromMethodNotSupported(__METHOD__);
                    }

                    public function make(string $classType, AutowireableParameterSet $parameters = null) : object {
                        throw UnsupportedOperation::fromMethodNotSupported(__METHOD__);
                    }

                    public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                        throw UnsupportedOperation::fromMethodNotSupported(__METHOD__);
                    }

                    public function get(string $id) {
                        throw UnsupportedOperation::fromMethodNotSupported(__METHOD__);
                    }

                    public function has(string $id) : bool {
                        throw UnsupportedOperation::fromMethodNotSupported(__METHOD__);
                    }
                };
            }

            public function addParameterStore(ParameterStore $parameterStore) : void {
            }
        };

        $bootstrap = new Bootstrap(
            directoryResolver: $this->directoryResolver,
            containerFactory: $noOpContainerFactory
        );

        $bootstrap->addObserver($infoCapturingObserver);

        $bootstrap->bootstrapContainer();

        $logicalConstraints = [
            new DuplicateServiceName(),
            new NonPublicServiceDelegate(),
            new NonPublicServicePrepare()
        ];

        $validator = new LogicalConstraintValidator(...$logicalConstraints);

        $containerDefinition = $infoCapturingObserver->containerDefinition;
        assert($containerDefinition !== null);
        $results = $validator->validate($containerDefinition, ['default']);

        $output->stdout->write('Annotated Container Analysis');
        $output->stdout->br();
        $output->stdout->write('Configuration file: ' . $configFile);
        $output->stdout->write('Logical Constraints:');
        $output->stdout->br();

        foreach ($logicalConstraints as $logicalConstraint) {
            $output->stdout->write('- ' . $logicalConstraint::class);
        }

        $output->stdout->br();

        $banner = str_repeat('*', 80);
        if (count($results) === 0) {
            $output->stdout->write('<fg:green>No logical constraint violations were found!</fg:green>');
        } else {
            foreach ($results as $index => $result) {
                $violationType = $result->violationType->name;
                $violationMessage = $result->message;

                $index++;
                $output->stdout->write(sprintf('Violation #%d - <fg:red>%s</fg:red>', $index, $violationType));
                $output->stdout->write($banner);
                $output->stdout->br();
                $output->stdout->write($violationMessage);
                $output->stdout->br();
            }

            $output->stdout->write(sprintf('<bold><fg:red>ERROR!</fg:red></bold> Total violations found: <bold>%d</bold>', count($results)));
        }

        return 0;
    }
}