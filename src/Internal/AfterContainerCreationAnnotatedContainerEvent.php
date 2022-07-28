<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\AnnotatedContainerEvent;
use Cspray\AnnotatedContainer\AnnotatedContainerLifecycle;

final class AfterContainerCreationAnnotatedContainerEvent implements AnnotatedContainerEvent {

    public function __construct(
        private readonly AnnotatedContainer $container
    ) {}

    public function getLifecycle() : AnnotatedContainerLifecycle {
        return AnnotatedContainerLifecycle::AfterContainerCreation;
    }

    public function getTarget() : AnnotatedContainer {
        return $this->container;
    }
}