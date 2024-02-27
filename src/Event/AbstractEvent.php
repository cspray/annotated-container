<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event;

/**
 * @template EventTarget
 * @implements Event<EventTarget>
 */
abstract class AbstractEvent implements Event {

    /**
     * @var EventTarget
     */
    private $target;

    /**
     * @param EventName $eventName
     * @param EventTarget $target
     */
    public function __construct(
        private readonly EventName $eventName,
        $target
    ) {
        $this->target = $target;
    }

    public function name() : EventName {
        return $this->eventName;
    }

    /**
     * @return EventTarget
     */
    public function target() {
        return $this->target;
    }

}