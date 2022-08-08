<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Internal\AfterContainerCreationAnnotatedContainerEvent;
use Cspray\AnnotatedContainer\Internal\BeforeContainerCreationAnnotatedContainerEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

final class EventEmittingContainerFactory implements ContainerFactory {

    public function __construct(
        private readonly ContainerFactory $factory,
        private readonly AnnotatedContainerEmitter $emitter
    ) {}

    public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : AnnotatedContainer {
        $this->emitter->trigger(new BeforeContainerCreationAnnotatedContainerEvent($containerDefinition));
        $container = $this->factory->createContainer($containerDefinition, $containerFactoryOptions);
        $this->emitter->trigger(new AfterContainerCreationAnnotatedContainerEvent($container));
        return $container;
    }

    public function addParameterStore(ParameterStore $parameterStore) : void {
        $this->factory->addParameterStore($parameterStore);
    }

    public function setLogger(LoggerInterface $logger) : void {
        $this->factory->setLogger($logger);
    }
}