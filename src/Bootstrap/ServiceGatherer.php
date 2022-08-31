<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

/**
 * @template Service
 */
interface ServiceGatherer {

    /**
     * @param class-string<Service> $type
     * @return Service[]
     */
    public function getServicesForType(string $type) : array;

}