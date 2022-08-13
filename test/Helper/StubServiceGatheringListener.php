<?php

namespace Cspray\AnnotatedContainer\Helper;

use Cspray\AnnotatedContainer\ServiceGatheringListener;
use Cspray\Typiphy\ObjectType;

final class StubServiceGatheringListener extends ServiceGatheringListener {

    private array $services = [];

    public function __construct(
        private readonly ObjectType $serviceType
    ) {}

    protected function doServiceGathering() : void {
        foreach ($this->getServicesOfType($this->serviceType) as $service) {
            $this->services[] = $service;
        }
    }

    public function getServices() : array {
        return $this->services;
    }
}