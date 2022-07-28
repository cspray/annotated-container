<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface AnnotatedContainerEvent {

    public function getLifecycle() : AnnotatedContainerLifecycle;

    public function getTarget() : ContainerDefinition|AnnotatedContainer|null;

}