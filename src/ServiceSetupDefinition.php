<?php declare(strict_types=1);


namespace Cspray\AnnotatedInjector;

/**
 * Defines a method that should be invoked when the given type has been created by the Injector.
 *
 * @package Cspray\AnnotatedInjector
 */
final class ServiceSetupDefinition {

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