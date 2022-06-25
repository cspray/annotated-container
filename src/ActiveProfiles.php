<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * An implicitly shared Service provided by Annotated Container that provides the list of active profiles when the
 * Container was created.
 */
interface ActiveProfiles {

    /**
     * Returns a list of profiles that were marked as active.
     *
     * @return string[]
     */
    public function getProfiles() : array;

    /**
     * Determine whether $profile is included in the list of active profiles.
     *
     * @param string $profile
     * @return bool
     */
    public function isActive(string $profile) : bool;

}