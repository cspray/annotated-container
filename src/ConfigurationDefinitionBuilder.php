<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\Typiphy\ObjectType;

final class ConfigurationDefinitionBuilder {

    private ObjectType $classType;

    private function __construct() {}

    public static function forClass(ObjectType $objectType) : self {
        $instance = new self;
        $instance->classType = $objectType;
        return $instance;
    }

    public function build() : ConfigurationDefinition {
        return new class($this->classType) implements ConfigurationDefinition {
            public function __construct(private readonly ObjectType $classType) {}

            public function getClass() : ObjectType {
                return $this->classType;
            }
        };
    }

}