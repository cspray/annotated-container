<?php

namespace Cspray\AnnotatedContainer\Event;

use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\Typiphy\ObjectType;

abstract class ServiceGatheringListener implements AnnotatedContainerListener {

    private ContainerDefinition $containerDefinition;
    private AnnotatedContainer $container;

    final public function handle(AnnotatedContainerEvent $event) : void {
        if ($event->getLifecycle() === AnnotatedContainerLifecycle::BeforeContainerCreation) {
            $containerDefinition = $event->getTarget();
            assert($containerDefinition instanceof ContainerDefinition);
            $this->containerDefinition = $containerDefinition;
        } else if ($event->getLifecycle() === AnnotatedContainerLifecycle::AfterContainerCreation) {
            $container = $event->getTarget();
            assert($container instanceof AnnotatedContainer);
            $this->container = $container;
            $this->doServiceGathering();
        }
    }

    final protected function getServicesOfType(ObjectType $objectType) : \Generator {
        foreach ($this->containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->isAbstract()) {
                continue;
            }

            /** @var class-string $objectName */
            $objectName = $objectType->getName();
            if (is_a($serviceDefinition->getType()->getName(), $objectName, true)) {
                yield $this->container->get($serviceDefinition->getType()->getName());
            }
        }
    }

    abstract protected function doServiceGathering() : void;

}