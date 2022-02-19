<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal\Interrogator;

use Cspray\AnnotatedContainer\ServicePrepareDefinition;
use Generator;

final class ServicePrepareDefinitionInterrogator {

    private ServiceDefinitionInterrogator $serviceDefinitionInterrogator;
    private array $servicePrepareDefinitions;

    public function __construct(
        ServiceDefinitionInterrogator $serviceDefinitionInterrogator,
        ServicePrepareDefinition... $servicePrepareDefinitions
    ) {
        $this->serviceDefinitionInterrogator = $serviceDefinitionInterrogator;
        $this->servicePrepareDefinitions = $servicePrepareDefinitions;
    }

    public function gatherServicePrepare() : Generator {
        $goodDefinitions = [];
        foreach ($this->servicePrepareDefinitions as $servicePrepareDefinition) {
            $serviceDefinition = $this->serviceDefinitionInterrogator->findServiceDefinitionForType($servicePrepareDefinition->getType());
            if (!isset($serviceDefinition)) {
                // This technically isn't a "good" definition but the compilation process
                $goodDefinitions[] = $servicePrepareDefinition;
            } else if ($serviceDefinition->isInterface()) {
                $goodDefinitions[] = $servicePrepareDefinition;
            } else if (empty($serviceDefinition->getImplementedServices())) {
                $goodDefinitions[] = $servicePrepareDefinition;
            } else {
                // we need to account for the scenario that a class has a ServicePrepare attribute while the Service
                // interface it implements does not. this is likely a code smell but we have to consider the possibility
                // we cannot simply look at goodDefinitions because we don't know for certain that the interface we care
                // about has been parsed and added to the collection yet
                // TODO: consider whether we should log a warning if this happens
                foreach ($serviceDefinition->getImplementedServices() as $implementedService) {
                    $hasInterfaceSetup = false;
                    foreach ($this->servicePrepareDefinitions as $_servicePrepareDefinition) {
                        // this is almost certainly brittle code that could be susceptible to failure where a Service
                        // implements multiple other Service interfaces with one or more ServicePrepare methods attributed
                        // TODO: determine a more thorough test case and make this code less brittle
                        if ($_servicePrepareDefinition->getType() === $implementedService->getType()) {
                            $hasInterfaceSetup = true;
                        }
                    }

                    // we take the stance that if the interface has a ServicePrepare method it should be the one that
                    // gets set for the prepare statement to ensure that all implementations get this method called.
                    // if the interface does have this ServicePrepare annotation we need to make sure the class specific
                    // one isn't erroneously invoked a second time in a prepares for that specific type
                    if (!$hasInterfaceSetup) {
                        $goodDefinitions[] = $servicePrepareDefinition;
                    }
                }
            }
        }
        yield from $goodDefinitions;
    }

}