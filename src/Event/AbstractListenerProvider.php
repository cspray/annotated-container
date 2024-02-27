<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event;

use Closure;

/**
 * @template EventTarget
 * @implements ListenerProvider<EventTarget>
 */
abstract class AbstractListenerProvider implements ListenerProvider {

    public function __construct(
        private readonly array $eventNames,
        private readonly Closure $closure
    ) {}

    /**
     * @return Listener<EventTarget>
     */
    public function getListener() : Listener {
        // TODO: Implement getListener() method.
    }

}