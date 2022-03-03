<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * Define the concrete Service that should be used when constructing an abstract Service.
 *
 * @see AliasDefinitionBuilder
 */
interface AliasDefinition {

    /**
     * An abstract Service used by your application but cannot be constructed directly.
     *
     * @return ServiceDefinition
     */
    public function getAbstractService() : ServiceDefinition;

    /**
     * The concrete Service that should be used where your applications requires the corresponding abstract Service.
     *
     * @return ServiceDefinition
     */
    public function getConcreteService() : ServiceDefinition;

    /**
     * Returns whether the given $aliasDefinition has matching abstract and concrete services.
     *
     * @param AliasDefinition $aliasDefinition
     * @return bool
     */
    public function equals(AliasDefinition $aliasDefinition) : bool;

}