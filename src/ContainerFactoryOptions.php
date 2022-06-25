<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * A set of options used by a ContainerFactory when creating your Container.
 *
 * @see ContainerFactoryOptionsBuilder
 */
interface ContainerFactoryOptions {

    /**
     * A list of profiles that should be considered active.
     *
     * @return string[]
     */
    public function getActiveProfiles() : array;

}