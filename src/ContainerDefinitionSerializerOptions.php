<?php

namespace Cspray\AnnotatedContainer;

/**
 * Options that control how a ContainerDefinition is serialized.
 */
final class ContainerDefinitionSerializerOptions {

    private bool $prettyFormatting = false;

    /**
     * Whether to output the serialized ContainerDefinition in a human-readable format.
     *
     * @return bool
     */
    public function isPrettyFormatted() : bool {
        return $this->prettyFormatting;
    }

    public function withPrettyFormatting() : self {
        $new = clone $this;
        $new->prettyFormatting = true;
        return $new;
    }

}