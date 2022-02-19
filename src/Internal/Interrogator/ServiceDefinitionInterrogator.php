<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal\Interrogator;

use Cspray\AnnotatedContainer\AliasDefinition;
use Cspray\AnnotatedContainer\ServiceDefinition;
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
            if (empty($serviceDefinition->getProfiles()) || in_array($this->environment, $serviceDefinition->getProfiles())) {
                yield $serviceDefinition;
            }
        }
    }

    public function gatherAliases() : Generator {
        foreach ($this->serviceDefinitions as $serviceDefinition) {
            if ($serviceDefinition->isClass()) {
                $hasImplementations = !empty($serviceDefinition->getImplementedServices());
                $hasExtendeds = !empty($serviceDefinition->getExtendedServices());
                $forEnvironment = empty($serviceDefinition->getProfiles()) || in_array($this->environment, $serviceDefinition->getProfiles());
                if ($hasImplementations && $forEnvironment) {
                    foreach ($serviceDefinition->getImplementedServices() as $implementedService) {
                        yield new AliasDefinition($implementedService, $serviceDefinition);
                    }
                } else if ($hasExtendeds && $forEnvironment) {
                    foreach ($serviceDefinition->getExtendedServices() as $extendedService) {
                        yield new AliasDefinition($extendedService, $serviceDefinition);
                    }
                }
            }
        }
    }

}