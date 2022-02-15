<?php

namespace Cspray\AnnotatedContainer;

class ContainerDefinitionSerializerOptions {

    private bool $prettyFormatting = false;

    public function isPrettyFormatted() : bool {
        return $this->prettyFormatting;
    }

    public function withPrettyFormatting() : self {
        $new = clone $this;
        $new->prettyFormatting = true;
        return $new;
    }

}