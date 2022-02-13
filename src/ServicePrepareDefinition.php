<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainer;

/**
 * Defines a method that should be invoked when the given type has been created by the Injector.
 *
 * @package Cspray\AnnotatedContainer
 */
final class ServicePrepareDefinition {

    public function __construct(
        private string $type,
        private string $method
    ) {}

    public function getType() : string {
        return $this->type;
    }

    public function getMethod() : string {
        return $this->method;
    }

}