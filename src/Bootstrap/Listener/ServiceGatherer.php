<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap\Listener;

interface ServiceGatherer {

    /**
     * @template T
     * @param class-string<T> $type
     * @return list<ServiceFromServiceDefinition>
     */
    public function servicesForType(string $type) : array;

    /**
     * @template T
     * @param class-string<T> $attributeType
     * @return list<ServiceFromServiceDefinition>
     */
    public function servicesWithAttribute(string $attributeType) : array;
}
