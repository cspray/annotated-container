<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

final class StandardAnnotatedContainerEmitter implements AnnotatedContainerEmitter {

    /**
     * @var list<AnnotatedContainerListener>
     */
    private array $listeners = [];

    public function registerListener(AnnotatedContainerListener $listener) : void {
        $this->listeners[] = $listener;
    }

    public function trigger(AnnotatedContainerEvent $event) : void {
        foreach ($this->listeners as $listener) {
            $listener->handle($event);
        }
    }

}