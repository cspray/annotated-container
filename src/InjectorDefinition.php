<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector;

use JsonSerializable;

/**
 * Interface InjectorDefinition
 * @package Cspray\AnnotatedInjector
 */
interface InjectorDefinition extends JsonSerializable {

    /**
     * Returns a set of ServiceDefinition that are shared with the Injector.
     *
     * Note that this IS NOT necessarily an exhaustive list of every class and interface annotated with Service. It is
     * possible, and likely, that a concrete implementation is listed as an alias or is not meant to be loaded with this
     * Injector due to the environment it is supposed to be running in.
     *
     * @return ServiceDefinition[]
     */
    public function getSharedServiceDefinitions() : array;

    /**
     * Returns a set of AliasDefinition that define which concrete implementations are meant to be used for a given
     * interface.
     *
     * Please note that as of 0.1.x multiple alias conflict resolution is not in place and it is possible to have
     * multiple AliasDefinitions for the same interface that could result in unexpected services from being injected.
     * Multiple alias conflict resolution is scheduled for the 0.2.x release series.
     *
     * @return AliasDefinition[]
     */
    public function getAliasDefinitions() : array;

    /**
     * @return ServiceSetupDefinition[]
     */
    public function getServiceSetup() : array;

}
