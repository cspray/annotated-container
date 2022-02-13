<?php

namespace Cspray\AnnotatedInjector\Internal\Interrogator;

use Cspray\AnnotatedInjector\ServiceDelegateDefinition;
use Generator;

final class ServiceDelegateDefinitionInterrogator {

    private array $serviceDelegateDefinitions;

    public function __construct(ServiceDelegateDefinition... $delegateDefinition) {
        $this->serviceDelegateDefinitions = $delegateDefinition;
    }

    public function getServiceDelegateDefinitions() : Generator {
        yield from $this->serviceDelegateDefinitions;
    }

}