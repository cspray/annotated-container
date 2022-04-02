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

}