<?php

namespace Cspray\AnnotatedContainer;

use Cspray\Typiphy\ObjectType;

final class ServiceDefinitionBuilder {

    private ?string $name = null;
    private ObjectType $type;
    private bool $isAbstract;
    private array $profiles = [];
    private bool $isPrimary = false;
    private bool $isShared = true;

    private function __construct() {}

    public static function forAbstract(ObjectType $type) : self {
        $instance = new self;
        $instance->type = $type;
        $instance->isAbstract = true;
        return $instance;
    }

    public static function forConcrete(ObjectType $type, bool $isPrimary = false) : self {
        $instance = new self;
        $instance->type = $type;
        $instance->isAbstract = false;
        $instance->isPrimary = $isPrimary;
        return $instance;
    }

    public function withName(string $name) : self {
        $instance = clone $this;
        $instance->name = $name;
        return $instance;
    }

    public function withProfiles(array $profiles) : self {
        $instance = clone $this;
        $instance->profiles = $profiles;
        return $instance;
    }

    public function withShared() : self {
        $instance = clone $this;
        $instance->isShared = true;
        return $instance;
    }

    public function withNotShared() : self {
        $instance = clone $this;
        $instance->isShared = false;
        return $instance;
    }

    public function build() : ServiceDefinition {
        $profiles = $this->profiles;
        return new class($this->name, $this->type, $this->isAbstract, $profiles, $this->isPrimary, $this->isShared) implements ServiceDefinition {

            public function __construct(
                private readonly ?string $name,
                private readonly ObjectType $type,
                private readonly bool $isAbstract,
                private readonly array $profiles,
                private readonly bool $isPrimary,
                private readonly bool $isShared
            ) {}

            public function getName() : ?string {
                return $this->name;
            }

            public function getType() : ObjectType {
                return $this->type;
            }

            public function getProfiles() : array {
                return $this->profiles;
            }

            public function isPrimary() : bool {
                return $this->isPrimary;
            }

            public function isConcrete() : bool {
                return !$this->isAbstract;
            }

            public function isAbstract() : bool {
                return $this->isAbstract;
            }

            public function isShared() : bool {
                return $this->isShared;
            }

            public function equals(ServiceDefinition $serviceDefinition): bool {
                return $serviceDefinition->getType() === $this->getType();
            }
        };
    }

}