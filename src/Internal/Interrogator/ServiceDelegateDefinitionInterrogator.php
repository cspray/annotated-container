<?php

namespace Cspray\AnnotatedContainer\Internal\Interrogator;

use Cspray\AnnotatedContainer\ServiceDelegateDefinition;
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