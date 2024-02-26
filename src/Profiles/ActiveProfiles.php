<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Profiles;

/**
 * An implicitly shared Service provided by Annotated Container that provides the list of active profiles when the
 * Container was created.
 *
 * @deprecated
 */
interface ActiveProfiles {

    /**
     * Returns a list of profiles that were marked as active.
     *
     * @return list<string>
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