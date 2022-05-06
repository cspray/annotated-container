<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Psr\Container\ContainerInterface;

/**
 * A factory that is responsible for turning a ContainerDefinition into a PSR ContainerInterface.
 */
interface ContainerFactory {

    /**
     * @param ContainerDefinition $containerDefinition
     * @param ContainerFactoryOptions|null $containerFactoryOptions
     * @return ContainerInterface
     */
    public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : ContainerInterface;

    /**
     * Assign a custom ParameterStore this ContainerFactory to allow injecting arbitrary values.
     *
     * @param ParameterStore $parameterStore
     * @return void
     */
    public function addParameterStore(ParameterStore $parameterStore) : void;

}