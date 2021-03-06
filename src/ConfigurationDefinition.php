<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\Typiphy\ObjectType;

/**
 * Defines a Configuration object.
 *
 * @see ConfigurationDefinitionBuilder
 */
interface ConfigurationDefinition {

    /**
     * The type of Configuration object.
     *
     * @return ObjectType
     */
    public function getClass() : ObjectType;

    /**
     * An optional "friendly" name used to identify the configuration.
     *
     * @return string|null
     */
    public function getName() : ?string;

}