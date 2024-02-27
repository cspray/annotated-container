<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event;

/**
 * @template EventTarget
 */
interface Listener {

    public function canHandle(EventName $eventName) : bool;

    /**
     * @param Event<EventTarget> $event
     */
    public function handle(Event $event) : void;

}