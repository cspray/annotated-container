<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal\Interrogator;

use Cspray\AnnotatedContainer\AliasDefinition;
use Cspray\AnnotatedContainer\ServiceDefinition;
use Generator;

final class ServiceDefinitionInterrogator {

    private array $profiles;
    private array $serviceDefinitions;

    public function __construct(array $profiles, ServiceDefinition... $serviceDefinitions) {
        $this->profiles = $profiles;
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
            if ($this->isServiceDefinitionForActiveProfile($serviceDefinition)) {
                yield $serviceDefinition;
            }
        }
    }

    public function gatherAliases() : Generator {
        foreach ($this->serviceDefinitions as $serviceDefinition) {
            if ($serviceDefinition->isClass()) {
                $hasImplementations = !empty($serviceDefinition->getImplementedServices());
                $hasExtendeds = !empty($serviceDefinition->getExtendedServices());
                $forEnvironment = $this->isServiceDefinitionForActiveProfile($serviceDefinition);
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

    private function isServiceDefinitionForActiveProfile(ServiceDefinition $serviceDefinition) : bool {
        foreach ($this->profiles as $activeProfile) {
            if (in_array($activeProfile, $serviceDefinition->getProfiles())) {
                return true;
            }
        }

        return false;
    }

}