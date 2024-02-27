<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event;

/**
 * @template EventTarget
 */
interface ListenerProvider {

    /**
     * @return Listener<EventTarget>
     */
    public function getListener() : Listener;

}