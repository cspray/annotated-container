<?php declare(strict_types=1);


namespace Cspray\AnnotatedInjector\Interrogator;


use Cspray\AnnotatedInjector\AliasDefinition;
use Cspray\AnnotatedInjector\ServiceDefinition;
use Generator;

final class ServiceDefinitionInterrogator {

    private string $environment;
    private array $serviceDefinitions;

    public function __construct(string $environment, ServiceDefinition... $serviceDefinitions) {
        $this->environment = $environment;
        $this->serviceDefinitions = $serviceDefinitions;
    }

    public function findServiceDefinitionForType(string $type) : ?ServiceDefinition {
        foreach ($this->serviceDefinitions as $serviceDefinition) {
            if ($type === $serviceDefinition->getType()) {
                return $serviceDefinition;
            }
        }

        return null;
    }

    public function gatherSharedServices() : Generator {
        foreach ($this->serviceDefinitions as $serviceDefinition) {
            if ($serviceDefinition->isInterface()) {
                yield $serviceDefinition;
            } else if (empty($serviceDefinition->getImplementedServices())) {
                yield $serviceDefinition;
            }
        }
    }

    public function gatherAliases() : Generator {
        foreach ($this->serviceDefinitions as $serviceDefinition) {
            if ($serviceDefinition->isClass()) {
                $hasImplementations = !empty($serviceDefinition->getImplementedServices());
                $forEnvironment = empty($serviceDefinition->getEnvironments()) || in_array($this->environment, $serviceDefinition->getEnvironments());
                if ($hasImplementations && $forEnvironment) {
                    yield new AliasDefinition($serviceDefinition->getImplementedServices()[0], $serviceDefinition);
                }
            }
        }
    }

}