<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Helper;

use Cspray\AnnotatedContainer\AnnotatedContainerEvent;
use Cspray\AnnotatedContainer\AnnotatedContainerListener;

final class StubAnnotatedContainerListener implements AnnotatedContainerListener {

    private array $events = [];

    public function handle(AnnotatedContainerEvent $event) : void {
        $this->events[] = $event;
    }

    public function getEvents() : array {
        return $this->events;
    }

}