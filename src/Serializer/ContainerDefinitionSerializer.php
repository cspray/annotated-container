<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Serializer;

use Cspray\AnnotatedContainer\ContainerDefinition;

/**
 * Allow for turning a ContainerDefinition into a format that can be persisted and later turned back into a
 * ContainerDefinition.
 */
interface ContainerDefinitionSerializer {

    /**
     * Convert a ContainerDefinition into a string representation.
     *
     * The precise format of the returned string will be dependent on the specific serializer used.
     *
     * @param ContainerDefinition $containerDefinition
     * @return string
     */
    public function serialize(ContainerDefinition $containerDefinition) : string;

    /**
     * Convert a string into a ContainerDefinition.
     *
     * The $serializedDefinition passed in should be the result of a call to serialize() from the same implementation.
     *
     * @param string $serializedDefinition
     * @return ContainerDefinition
     */
    public function deserialize(string $serializedDefinition) : ContainerDefinition;

}