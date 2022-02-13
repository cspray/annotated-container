<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * Defines a Service, a class or interface that should be shared or aliased on the wired Injector.
 *
 * @package Cspray\AnnotatedInjector
 */
final class ServiceDefinition {

    public function __construct(
        private string $type,
        private array $environments,
        private array $implementedServices,
        private array $extendedServices,
        private bool $isInterface,
        private bool $isAbstract,
    ) {}

    public function getType() : string {
        return $this->type;
    }

    /**
     * Returns an array of ServiceDefinition for each Service interface implemented.
     *
     * Please note that this IS NOT an exhaustive list of every possible interface for the given $type. Instead it only
     * lists those that interfaces that the $type implements that are also annotated with the Service attribute.
     *
     * @return ServiceDefinition[]
     */
    public function getImplementedServices() : array {
        return $this->implementedServices;
    }

    /**
     * @return ServiceDefinition[]
     */
    public function getExtendedServices() : array {
        return $this->extendedServices;
    }

    public function getEnvironments() : array {
        return $this->environments;
    }

    public function isInterface() : bool {
        return $this->isInterface;
    }

    public function isClass() : bool {
        return !$this->isInterface;
    }

    public function isAbstract() : bool {
        return $this->isClass() && $this->isAbstract;
    }

}