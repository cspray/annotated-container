<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;

final class ServiceDefinitionBuilder {

    private string $type;
    private bool $isAbstract;
    private array $implementedServices = [];
    private ?AnnotationValue $profiles = null;
    private bool $isPrimary = false;

    private function __construct() {}

    /**
     * @param string $type
     * @return static
     * @throws DefinitionBuilderException
     */
    public static function forAbstract(string $type) : self {
        if (empty($type)) {
            throw new DefinitionBuilderException(sprintf(
                'Must not pass an empty type to %s',
                __METHOD__
            ));
        }
        $instance = new self;
        $instance->type = $type;
        $instance->isAbstract = true;
        return $instance;
    }

    /**
     * @param string $type
     * @return static
     * @throws DefinitionBuilderException
     */
    public static function forConcrete(string $type, bool $isPrimary = false) : self {
        if (empty($type)) {
            throw new DefinitionBuilderException(sprintf(
                'Must not pass an empty type to %s',
                __METHOD__
            ));
        }
        $instance = new self;
        $instance->type = $type;
        $instance->isAbstract = false;
        $instance->isPrimary = $isPrimary;
        return $instance;
    }

    /**
     * @throws DefinitionBuilderException
     */
    public function withImplementedService(ServiceDefinition $serviceDefinition) : self {
        if ($this->isAbstract) {
            throw new DefinitionBuilderException(sprintf(
                'Attempted to add an implemented service to abstract type %s which is not allowed.',
                $this->type
            ));
        } else if (!$serviceDefinition->isAbstract()) {
            throw new DefinitionBuilderException(sprintf(
                'Attempted to add a concrete implemented service to a concrete type %s which is not allowed.',
                $this->type
            ));
        }
        $instance = clone $this;
        $instance->implementedServices[] = $serviceDefinition;
        return $instance;
    }

    public function withProfiles(AnnotationValue $profiles) : self {
        $instance = clone $this;
        $instance->profiles = $profiles;
        return $instance;
    }

    public function build() : ServiceDefinition {
        $profiles = $this->profiles;
        if (is_null($profiles)) {
            $profiles = new ArrayAnnotationValue();
        }
        return new class($this->type, $this->isAbstract, $this->implementedServices, $profiles, $this->isPrimary) implements ServiceDefinition {

            private string $type;
            private bool $isAbstract;
            private array $implementedServices;
            private AnnotationValue $profiles;
            private bool $isPrimary;

            public function __construct(string $type, bool $isAbstract, array $implementedServices, AnnotationValue $profiles, bool $isPrimary) {
                $this->type = $type;
                $this->isAbstract = $isAbstract;
                $this->implementedServices = $implementedServices;
                $this->profiles = $profiles;
                $this->isPrimary = $isPrimary;
            }

            public function getType(): string {
                return $this->type;
            }

            public function getImplementedServices(): array {
                return $this->implementedServices;
            }

            public function getProfiles(): AnnotationValue {
                return $this->profiles;
            }

            public function isPrimary(): bool {
                return $this->isPrimary;
            }

            public function isConcrete(): bool {
                return !$this->isAbstract;
            }

            public function isAbstract(): bool {
                return $this->isAbstract;
            }

            public function equals(ServiceDefinition $serviceDefinition): bool {
                return $serviceDefinition->getType() === $this->getType();
            }
        };
    }

}