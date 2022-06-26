<?php

namespace Cspray\AnnotatedContainer;

use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;
use Psr\Container\ContainerInterface;

final class ServiceCollectorParameterStore implements ParameterStore {

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly array $serviceTypes
    ) {}

    public function getName() : string {
        return 'service-collector';
    }

    public function fetch(TypeUnion|Type|TypeIntersect $type, string $key) : array {
        $services = [];
        foreach ($this->serviceTypes as $serviceType) {
            if (is_subclass_of($serviceType, $key)) {
                $services[] = $this->container->get($serviceType);
            }
        }
        return $services;
    }
}