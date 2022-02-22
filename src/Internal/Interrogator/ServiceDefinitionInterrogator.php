<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal\Interrogator;

use Cspray\AnnotatedContainer\AliasDefinition;
use Cspray\AnnotatedContainer\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\ServiceDefinition;
use Generator;

final class ServiceDefinitionInterrogator {

    private array $profiles;
    private array $serviceDefinitions;

    public function __construct(array $profiles, ServiceDefinition... $serviceDefinitions) {
        $this->profiles = $profiles;
        $this->serviceDefinitions = $serviceDefinitions;
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
            if ($serviceDefinition->isConcrete()) {
                $hasImplementations = !empty($serviceDefinition->getImplementedServices());
                $forEnvironment = $this->isServiceDefinitionForActiveProfile($serviceDefinition);
                if ($hasImplementations && $forEnvironment) {
                    foreach ($serviceDefinition->getImplementedServices() as $implementedService) {
                        yield AliasDefinitionBuilder::forAbstract($implementedService)->withConcrete($serviceDefinition)->build();
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