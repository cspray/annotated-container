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
     * Note that this IS NOT necessarily an exhaustive list of every class and interface annotated with Service. It is
<<<<<<< HEAD
     * possible, and likely, that a concrete implementation is marked as belonging to a profile that isn't currently
     * active. This, and potentially other valid reasons, might exclude a type annotated with Service from appearing
     * this collection.
=======
     * possible, and likely, that a concrete implementation is listed as an alias or is not meant to be loaded with this
     * Injector due to the active profiles it is supposed to be running in.
>>>>>>> 84ef297 (Start adding better docs)
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

    /**
     * Returns a set of InjectScalarDefinition that determine what values to use when a service parameter requires a
     * scalar or non-object value injected into it.
     *
     * @return InjectScalarDefinition[]
     */
    public function getInjectScalarDefinitions() : array;

    /**
     * Returns a set of InjectServiceDefinition that determine a specific service to inject into a parameter where
     * normal alias resolution might not be possible.
     *
     * @return InjectServiceDefinition[]
     */
    public function getInjectServiceDefinitions() : array;

    /**
     * Returns a set of ServiceDelegateDefinition that determine which services require factories to be constructed.
     *
     * @return ServiceDelegateDefinition[]
     */
    public function getServiceDelegateDefinitions() : array;

}
