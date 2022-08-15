<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event;

use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\AnnotatedContainer;

interface AnnotatedContainerEvent {

    public function getLifecycle() : AnnotatedContainerLifecycle;

    public function getTarget() : ContainerDefinition|AnnotatedContainer|null;

}