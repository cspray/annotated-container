<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event;

use Cspray\AnnotatedContainer\ContainerDefinition;
use Cspray\AnnotatedContainer\AnnotatedContainer;

/**
 * @deprecated This class is designated to be removed in 2.0
 */
interface AnnotatedContainerEvent {

    public function getLifecycle() : AnnotatedContainerLifecycle;

    public function getTarget() : ContainerDefinition|AnnotatedContainer|null;

}