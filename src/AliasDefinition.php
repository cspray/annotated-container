<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * Defines the ServiceDefinition that should be used to generate aliases on the wired Injector.
 *
 * @package Cspray\AnnotatedInjector
 */
final class AliasDefinition {

    public function __construct(
        private ServiceDefinition $originalService,
        private ServiceDefinition $aliasService
    ) {}

    public function getOriginalServiceDefinition() : ServiceDefinition {
        return $this->originalService;
    }

    public function getAliasServiceDefinition() : ServiceDefinition {
        return $this->aliasService;
    }


}