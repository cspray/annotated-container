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
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceDelegate;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceName;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServicePrepare;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceType;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\MultiplePrimaryForAbstractService;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\NonPublicServiceDelegate;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\NonPublicServicePrepare;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraint;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintValidator;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

final class ValidateCommand implements Command {

    /**
     * @var list<LogicalConstraint>
     */
    private readonly array $logicalConstraints;

    private readonly LogicalConstraintValidator $validator;

    public function __construct(
        private readonly BootstrappingDirectoryResolver $directoryResolver
    ) {
        $this->logicalConstraints = [
            new DuplicateServiceDelegate(),
            new DuplicateServiceName(),
            new DuplicateServicePrepare(),
            new DuplicateServiceType(),
            new MultiplePrimaryForAbstractService(),
            new NonPublicServiceDelegate(),
            new NonPublicServicePrepare()
        ];

        $this->validator = new LogicalConstraintValidator(...$this->logicalConstraints);
    }

    public function getName() : string {
        return 'validate';
    }

    public function getHelp() : string {
        return <<<TEXT
NAME

    validate - Ensure container definition validates against all logical constraints.
    
SYNOPSIS

    <bold>validate</bold> [OPTION]...

DESCRIPTION

    <bold>validate</bold> will analyze your codebase, run a series of Logical Constraint 
    checks, and output any violations found.
    
    Violations are split into three different types:
    
    - Critical
        These errors are highly indicative of a problem that will result in an exception 
        at runtime. It is HIGHLY recommended that these violations are fixed immediately.
        
    - Warning
        These errors are likely indicative of a problem that will result in an exception 
        or error at runtime, but may not based on various conditions. It is recommended 
        that these violations are fixed as soon as possible.
        
    - Notice
        These errors will not cause an exception or error at runtime, but are likely 
        indicative of some problem or misunderstanding in your dependency injection 
        configuration. You should try to fix these violations when possible.

OPTIONS

    --config-file="file-path.xml"

        Set the name of the configuration file to be used. If not provided the
        default value will be "annotated-container.xml".
        
    --list-constraints
    
        Show which logical constraints will be used to validate your container 
        definition. Passing this options will only list constraints, validation 
        will NOT run with this option passed. 

TEXT;
    }

    public function handle(Input $input, TerminalOutput $output) : int {
        if ($input->getOption('list-constraints') === true) {
            $this->listConstraints($output);
            return 0;
        }
        $configOption = $input->getOption('config-file')  ?? 'annotated-container.xml';
        $configFile = $this->directoryResolver->getConfigurationPath($configOption);
        if (!is_file($configFile)) {
            throw ConfigurationNotFound::fromMissingFile($configFile);
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

        $bootstrap->bootstrapContainer(
            configurationFile: $configOption
        );

        $containerDefinition = $infoCapturingObserver->containerDefinition;
        assert($containerDefinition !== null);
        $results = $this->validator->validate($containerDefinition, ['default']);

        $output->stdout->write('Annotated Container Validation');
        $output->stdout->br();
        $output->stdout->write('Configuration file: ' . $configFile);
        $output->stdout->br();
        $output->stdout->write('To view validations ran, execute "annotated-container validate --list-constraints"');
        $output->stdout->br();

        $banner = str_repeat('*', 80);
        if (count($results) === 0) {
            $output->stdout->write('<fg:green>No logical constraint violations were found!</fg:green>');
        } else {
            foreach ($results as $index => $result) {
                $violationType = $result->violationType->name;
                $violationMessage = trim($result->message);

                $violationColor = match ($result->violationType) {
                    LogicalConstraintViolationType::Critical => 'red',
                    LogicalConstraintViolationType::Warning => 'yellow',
                    default => 'red'
                };

                $index++;
                $output->stdout->write(sprintf('Violation #%1$d - <fg:%2$s>%3$s</fg:%2$s>', $index, $violationColor, $violationType));
                $output->stdout->write($banner);
                $output->stdout->br();
                $output->stdout->write($violationMessage);
                $output->stdout->br();
            }

            $output->stdout->write(sprintf('<bold><fg:red>ERROR!</fg:red></bold> Total violations found: <bold>%d</bold>', count($results)));
        }

        return 0;
    }

    private function listConstraints(TerminalOutput $output) : void {
        $output->stdout->write('Annotated Container Validation');
        $output->stdout->br();
        $output->stdout->write('The following constraints will be checked when validate is ran:');
        $output->stdout->br();

        foreach ($this->logicalConstraints as $logicalConstraint) {
            $output->stdout->write('- ' . $logicalConstraint::class);
        }
    }
}