<?php

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\DefinitionBuilderException;

final class ServiceDefinitionBuilder {

    private ?AnnotationValue $name = null;
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

    public function withName(AnnotationValue $name) : self {
        $instance = clone $this;
        $instance->name = $name;
        return $instance;
    }

    public function withProfiles(CollectionAnnotationValue $profiles) : self {
        $instance = clone $this;
        $instance->profiles = $profiles;
        return $instance;
    }

    public function build() : ServiceDefinition {
        $profiles = $this->profiles;
        if (is_null($profiles)) {
            $profiles = arrayValue([]);
        }
        return new class($this->name, $this->type, $this->isAbstract, $profiles, $this->isPrimary) implements ServiceDefinition {

            public function __construct(
                private ?AnnotationValue $name,
                private string $type,
                private bool $isAbstract,
                private CollectionAnnotationValue $profiles,
                private bool $isPrimary
            ) {}

            public function getName() : ?AnnotationValue {
                return $this->name;
            }

            public function getType(): string {
                return $this->type;
            }

            public function getProfiles(): CollectionAnnotationValue {
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