<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\ContainerDefinitionMergeException;

/**
 * The heart of the AnnotatedContainer; the ContainerDefinition, an object which defines how a Container should be
 * configured.
 *
 * @see ContainerDefinitionBuilder
 */
interface ContainerDefinition {

    /**
     * An immutable method that will return new ContainerDefinition that has the contents of this ContainerDefinition and
     * the passed $containerDefinition.
     *
     * @param ContainerDefinition $containerDefinition
     * @return ContainerDefinition
     * @throws ContainerDefinitionMergeException An exception that can be thrown if the given $containerDefinition can't be merged for some reason
     */
    public function merge(ContainerDefinition $containerDefinition) : ContainerDefinition;

    /**
     * Return a set of ServiceDefinitions that this Container is aware of.
     *
     * @return ServiceDefinition[]
     */
    public function getServiceDefinitions() : array;

    /**
     * Returns a set of AliasDefinition that define which concrete services are possible candidate for a given abstract
     * service.
     *
     * Note that it is possible for an abstract service to have multiple AliasDefinition defined for it. It is not the
     * job of the ContainerDefinition to determine what should be done in this situation; it is meant to simply provide
     * the results of a configuration for a Container and an annotated configuration is possible to have multiple
     * concrete services that could satisfy an abstract service. It is the responsibility of the ContainerFactory
     * or a LogicalConstraintValidator to recognize multiple aliases are possible and take the appropriate steps.
     *
     * @return AliasDefinition[]
     */
    public function getAliasDefinitions() : array;

    /**
     * Returns a set of ServicePrepareDefinition that determine which service methods will be automatically invoked
     * after service construction.
     *
     * @return ServicePrepareDefinition[]
     */
    public function getServicePrepareDefinitions() : array;

}
