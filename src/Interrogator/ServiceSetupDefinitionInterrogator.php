<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Interrogator;

use Cspray\AnnotatedInjector\ServiceSetupDefinition;
use Generator;

final class ServiceSetupDefinitionInterrogator {

    private ServiceDefinitionInterrogator $serviceDefinitionInterrogator;
    private array $serviceSetupDefinitions;

    public function __construct(
        ServiceDefinitionInterrogator $serviceDefinitionInterrogator,
        ServiceSetupDefinition... $serviceSetupDefinitions
    ) {
        $this->serviceDefinitionInterrogator = $serviceDefinitionInterrogator;
        $this->serviceSetupDefinitions = $serviceSetupDefinitions;
    }

    public function gatherServiceSetup() : Generator {
        $goodDefinitions = [];
        foreach ($this->serviceSetupDefinitions as $serviceSetupDefinition) {
            $serviceDefinition = $this->serviceDefinitionInterrogator->findServiceDefinitionForType($serviceSetupDefinition->getType());
            if ($serviceDefinition->isInterface()) {
                $goodDefinitions[] = $serviceSetupDefinition;
            } else {
                // we need to account for the scenario that a class has a ServiceSetup attribute while the Service
                // interface it implements does not. this is likely a code smell but we have to consider the possibility
                // we cannot simply look at goodDefinitions because we don't know for certain that the interface we care
                // about has been parsed and added to the collection yet
                // TODO: consider whether we should log a warning if this happens
                foreach ($serviceDefinition->getImplementedServices() as $implementedService) {
                    $hasInterfaceSetup = false;
                    foreach ($this->serviceSetupDefinitions as $_serviceSetupDefinition) {
                        // this is almost certainly brittle code that could be susceptible to failure where a Service
                        // implements multiple other Service interfaces with one or more ServiceSetup methods attributed
                        // TODO: determine a more thorough test case and make this code less brittle
                        if ($_serviceSetupDefinition->getType() === $implementedService->getType()) {
                            $hasInterfaceSetup = true;
                        }
                    }

                    // we take the stance that if the interface has a ServiceSetup method it should be the one that
                    // gets set for the prepare statement to ensure that all implementations get this method called.
                    // if the interface does have this ServiceSetup annotation we need to make sure the class specific
                    // one isn't erroneously invoked a second time in a prepares for that specific type
                    if (!$hasInterfaceSetup) {
                        $goodDefinitions[] = $serviceSetupDefinition;
                    }
                }
            }
        }
        yield from $goodDefinitions;
    }

}