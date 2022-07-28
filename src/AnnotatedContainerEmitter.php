<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface AnnotatedContainerEmitter {

    public function registerListener(AnnotatedContainerListener $listener) : void;

    public function trigger(AnnotatedContainerEvent $event) : void;

}