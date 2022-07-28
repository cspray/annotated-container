<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\AnnotatedContainerEvent;
use Cspray\AnnotatedContainer\AnnotatedContainerLifecycle;
use Cspray\AnnotatedContainer\ContainerDefinition;

final class AfterCompileAnnotatedContainerEvent implements AnnotatedContainerEvent {

    public function __construct(
        private readonly ContainerDefinition $containerDefinition
    ) {}

    public function getLifecycle() : AnnotatedContainerLifecycle {
        return AnnotatedContainerLifecycle::AfterCompile;
    }

    public function getTarget() : ContainerDefinition {
        return $this->containerDefinition;
    }
}