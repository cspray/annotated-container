<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * Defines a Service, a class or interface that should be shared or aliased on the wired Injector.
 *
 * @package Cspray\AnnotatedContainer
 */
interface ServiceDefinition {

    /**
     * @return AnnotationValue|null
     */
    public function getName() : ?AnnotationValue;

    /**
     * Returns the fully-qualified class/interface name for the given Service.
     *
     * @return string
     */
    public function getType() : string;

    /**
     * Returns an array of profiles that this service is attached to.
     *
     * A ServiceDefinition MUST have at least 1 profile; if a profile is not explicitly set for a given Service it should
     * be given the 'default' profile.
     *
     * @return CollectionAnnotationValue
     */
    public function getProfiles() : CollectionAnnotationValue;

    /**
     * Return whether the Service is the Primary for this type and will be used by default if there are multiple aliases
     * resolved.
     *
     * @return bool
     */
    public function isPrimary() : bool;

    /**
     * Returns whether the defined Service is a concrete class that can be instantiated.
     *
     * @return bool
     */
    public function isConcrete() : bool;

    /**
     * Returns whether the defined Service is an abstract class or interface that cannot be instantiated.
     *
     * @return bool
     */
    public function isAbstract() : bool;

    /**
     * @return bool
     */
    public function isShared() : bool;

    public function equals(ServiceDefinition $serviceDefinition) : bool;

}