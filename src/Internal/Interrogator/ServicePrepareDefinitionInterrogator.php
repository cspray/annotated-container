<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal\Interrogator;

use Cspray\AnnotatedContainer\ServicePrepareDefinition;
use Generator;

final class ServicePrepareDefinitionInterrogator {

    private array $servicePrepareDefinitions;

    public function __construct(
        ServicePrepareDefinition... $servicePrepareDefinitions
    ) {
        $this->servicePrepareDefinitions = $servicePrepareDefinitions;
    }

    public function gatherServicePrepare() : Generator {
        foreach ($this->servicePrepareDefinitions as $servicePrepareDefinition) {
            $serviceDefinition = $servicePrepareDefinition->getService();
            // If the service is abstract or if the service definition does not have any implemented services then we
            // can safely say the current service prepare is valid
            if ($serviceDefinition->isAbstract() || empty($serviceDefinition->getImplementedServices())) {
                yield $servicePrepareDefinition;
            } else {
                // we need to account for the scenario that a class has a ServicePrepare attribute while the Service
                // interface it implements does not. this is likely a code smell but we have to consider the possibility
                // because we don't know for certain that the interface we care about has been parsed
                // TODO: consider whether we should log a warning if this happens
                foreach ($serviceDefinition->getImplementedServices() as $implementedService) {
                    $hasInterfaceSetup = false;
                    foreach ($this->servicePrepareDefinitions as $_servicePrepareDefinition) {
                        // this is almost certainly brittle code that could be susceptible to failure where a Service
                        // implements multiple other Service interfaces with one or more ServicePrepare methods attributed
                        // TODO: determine a more thorough test case and make this code less brittle
                        if ($_servicePrepareDefinition->getService()->getType() === $implementedService->getType()) {
                            $hasInterfaceSetup = true;
                        }
                    }

                    // we take the stance that if the interface has a ServicePrepare method it should be the one that
                    // gets set for the prepare statement to ensure that all implementations get this method called.
                    // if the interface does have this ServicePrepare annotation we need to make sure the class specific
                    // one isn't erroneously invoked a second time in a prepares for that specific type
                    if (!$hasInterfaceSetup) {
                        yield $servicePrepareDefinition;
                    }
                }
            }
        }
    }

}