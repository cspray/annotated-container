<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\ContainerDefinition;

abstract class ServiceWiringObserver implements Observer {

    final public function beforeCompilation() : void {
        // noop
    }

    final public function afterCompilation(ContainerDefinition $containerDefinition) : void {
        // noop
    }

    final public function beforeContainerCreation(ContainerDefinition $containerDefinition) : void {
        // noop
    }

    final public function afterContainerCreation(ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        $serviceGatherer = new class($containerDefinition, $container) implements ServiceGatherer {

            public function __construct(
                private readonly ContainerDefinition $containerDefinition,
                private readonly AnnotatedContainer $container
            ) {}

            public function getServicesForType(string $type) : array {
                $services = [];
                foreach ($this->containerDefinition->getServiceDefinitions() as $serviceDefinition) {
                    if ($serviceDefinition->isAbstract()) {
                        continue;
                    }

                    /** @var class-string $serviceType */
                    $serviceType = $serviceDefinition->getType()->getName();
                    if (is_a($serviceType, $type, true)) {
                        $services[] = $this->container->get($serviceType);
                    }
                }

                return $services;
            }
        };
        $this->wireServices($container, $serviceGatherer);
    }

    abstract protected function wireServices(AnnotatedContainer $container, ServiceGatherer $gatherer) : void;

}