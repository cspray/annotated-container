<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface ActiveProfiles {

    public function getProfiles() : array;

    public function isActive(string $profile) : bool;

}