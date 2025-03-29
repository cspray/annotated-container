<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap\Configuration;

use Cspray\AnnotatedContainer\Event\Listener;

interface ListenerFactory {

    /**
     * @param string|class-string<Listener> $identifier
     * @return Listener
     */
    public function createListener(string $identifier) : Listener;
}
