<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event;

use DateTimeImmutable;

/**
 * @template Target
 */
interface Event {

    public function name() : EventName;

    /**
     * @return Target
     */
    public function target();

}