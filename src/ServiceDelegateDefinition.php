<?php

namespace Cspray\AnnotatedInjector;

final class ServiceDelegateDefinition {

    private string $delegateType;
    private string $delegateMethod;
    private string $serviceType;

    public function __construct(string $delegateType, string $delegateMethod, string $serviceType) {
        $this->delegateType = $delegateType;
        $this->delegateMethod = $delegateMethod;
        $this->serviceType = $serviceType;
    }

    public function getDelegateType() : string {
        return $this->delegateType;
    }

    public function getDelegateMethod() : string {
        return $this->delegateMethod;
    }

    public function getServiceType() : string {
        return $this->serviceType;
    }

}