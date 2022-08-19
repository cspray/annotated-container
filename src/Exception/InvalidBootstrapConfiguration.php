<?php

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\ContainerDefinitionBuilderContextConsumer;
use Cspray\AnnotatedContainer\ParameterStore;

final class InvalidBootstrapConfiguration extends Exception {

    public static function fromFileDoesNotValidateSchema(string $file) : self {
        $message = sprintf('Configuration file %s does not validate against the appropriate schema.', $file);
        return new self($message);
    }

    public static function fromConfiguredContainerDefinitionConsumerWrongType() : self {
        $message = sprintf(
            'All entries in containerDefinitionBuilderContextConsumers must be classes that implement %s',
            ContainerDefinitionBuilderContextConsumer::class
        );
        return new self($message);
    }

    public static function fromConfiguredParameterStoreWrongType() : self {
        $message = sprintf(
            'All entries in parameterStores must be classes that implement %s',
            ParameterStore::class
        );
        return new self($message);
    }

}