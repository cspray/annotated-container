<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

/**
 * @deprecated This class is designated to be removed in 2.0
 */
interface AnnotatedContainerEvent {

    public function getLifecycle() : AnnotatedContainerLifecycle;

    public function getTarget() : ContainerDefinition|AnnotatedContainer|null;

}