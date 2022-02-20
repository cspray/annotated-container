<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * Defines the ServiceDefinition that should be used to generate aliases on the wired Injector.
 *
 * @package Cspray\AnnotatedContainer
 */
interface AliasDefinition {

    public function getAbstractService() : ServiceDefinition;

    public function getConcreteService() : ServiceDefinition;

}