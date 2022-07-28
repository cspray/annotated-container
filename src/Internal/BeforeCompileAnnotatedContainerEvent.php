<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\AnnotatedContainerEvent;
use Cspray\AnnotatedContainer\AnnotatedContainerLifecycle;
use Cspray\AnnotatedContainer\ContainerDefinition;

final class BeforeCompileAnnotatedContainerEvent implements AnnotatedContainerEvent {

    public function __construct() {}

    public function getLifecycle() : AnnotatedContainerLifecycle {
        return AnnotatedContainerLifecycle::BeforeCompile;
    }

    public function getTarget() : ContainerDefinition|AnnotatedContainer|null {
        return null;
    }
}