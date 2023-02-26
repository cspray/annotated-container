<?php

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\Bootstrap\ContainerCreatedObserver;
use Cspray\AnnotatedContainer\Bootstrap\Observer;
use Cspray\AnnotatedContainer\Bootstrap\PostAnalysisObserver;
use Cspray\AnnotatedContainer\Bootstrap\PreAnalysisObserver;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;

final class InvalidBootstrapConfiguration extends Exception {

    public static function fromFileDoesNotValidateSchema(string $file) : self {
        $message = sprintf('Configuration file %s does not validate against the appropriate schema.', $file);
        return new self($message);
    }

    public static function fromConfiguredDefinitionProviderWrongType() : self {
        $message = sprintf(
            'All entries in definitionProviders must be classes that implement %s',
            DefinitionProvider::class
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

    public static function fromConfiguredObserverWrongType() : self {
        return new self(sprintf(
            'All entries in observers must be classes that implement %s, %s, or %s',
            PreAnalysisObserver::class,
            PostAnalysisObserver::class,
            ContainerCreatedObserver::class
        ));
    }

}