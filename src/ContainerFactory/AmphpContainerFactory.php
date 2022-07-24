<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Amp\Injector\Application;
use Amp\Injector\Definitions;
use Amp\Injector\Injector;
use Cspray\AnnotatedContainer\ActiveProfiles;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\AutowireableParameterSet;
use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactoryOptions;
use Cspray\AnnotatedContainer\ProfilesAwareContainerDefinition;
use function Amp\Injector\any;
use function Amp\Injector\arguments;
use function Amp\Injector\automaticTypes;
use function Amp\Injector\object;
use function Amp\Injector\singleton;
use function Amp\Injector\value;

class AmphpContainerFactory extends AbstractContainerFactory implements ContainerFactory {

    public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : AnnotatedContainer {
        $activeProfiles = $containerFactoryOptions?->getActiveProfiles() ?? [];
        if (empty($activeProfiles)) {
            $activeProfiles[] = 'default';
        }
        $definitions = new Definitions();

        $containerDefinition = new ProfilesAwareContainerDefinition($containerDefinition, $activeProfiles);
        $activeProfilesService = $this->getActiveProfilesService($activeProfiles);

        $definitions = $definitions->with(value($activeProfilesService), ActiveProfiles::class);

        foreach ($containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            $arguments = arguments();
            if ($serviceDefinition->isAbstract()) {
                $alias = $this->aliasDefinitionResolver->resolveAlias($containerDefinition, $serviceDefinition->getType());
            }
            $amphpDefinition = singleton(
                object(
                    $serviceDefinition->getType()->getName(),
                )
            );
            $definitions = $definitions->with(
                $amphpDefinition,
                $serviceDefinition->getType()->getName()
            );

            if ($serviceDefinition->getName() !== null) {
                $definitions = $definitions->with(
                    $amphpDefinition,
                    $serviceDefinition->getName()
                );
            }
        }

        $application = new Application(new Injector(any()), $definitions);
        $application->start();

        return new class($application) implements AnnotatedContainer {

            public function __construct(
                private readonly Application $application
            ) {}

            public function make(string $classType, AutowireableParameterSet $parameters = null) : object {

            }

            public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                // TODO: Implement invoke() method.
            }

            public function get(string $id) {
                return $this->application->getContainer()->get($id);
            }

            public function has(string $id) : bool {
                return $this->application->getContainer()->has($id);
            }

            public function getBackingContainer() : Application {
                return $this->application;
            }
        };
    }
}