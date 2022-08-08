<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Psr\Log\LoggerAwareInterface;

/**
 * A factory that is responsible for turning a ContainerDefinition into a PSR ContainerInterface.
 */
interface ContainerFactory extends LoggerAwareInterface {

    /**
     * @param ContainerDefinition $containerDefinition
     * @param ContainerFactoryOptions|null $containerFactoryOptions
     * @return AnnotatedContainer
     */
    public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : AnnotatedContainer;

    /**
     * Assign a custom ParameterStore this ContainerFactory to allow injecting arbitrary values.
     *
     * @param ParameterStore $parameterStore
     * @return void
     */
    public function addParameterStore(ParameterStore $parameterStore) : void;

}