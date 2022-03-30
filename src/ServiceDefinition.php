<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * Defines a Service, a class or interface that should be shared or aliased on the wired Injector.
 *
 * @package Cspray\AnnotatedContainer
 */
interface ServiceDefinition {

    /**
     * Returns the fully-qualified class/interface name for the given Service.
     *
     * @return string
     */
    public function getType() : string;

    /**
     * Returns an array of ServiceDefinition for each Service interface or abstract class implemented.
     *
     * Please note that this IS NOT an exhaustive list of every possible interface for the given $type. Instead, it only
     * lists those interfaces or abstract classes that the $type implements that are also annotated with the Service attribute.
     *
     * @return ServiceDefinition[]
     */
    public function getImplementedServices() : array;

    /**
     * Returns an array of profiles that this service is attached to.
     *
     * A ServiceDefinition MUST have at least 1 profile; if a profile is not explicitly set for a given Service it should
     * be given the 'default' profile.
     *
     * @return AnnotationValue
     */
    public function getProfiles() : AnnotationValue;

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

    public function equals(ServiceDefinition $serviceDefinition) : bool;

}