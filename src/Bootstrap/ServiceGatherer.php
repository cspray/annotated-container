<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

/**
 * @template T
 */
interface ServiceGatherer {

    /**
     * @param class-string<T> $type
     * @return ServiceFromServiceDefinition<T>[]
     */
    public function getServicesForType(string $type) : array;

}