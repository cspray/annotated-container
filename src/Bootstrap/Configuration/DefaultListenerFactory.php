<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap\Configuration;

use Cspray\AnnotatedContainer\Event\Listener;

final class DefaultListenerFactory implements ListenerFactory {

    /**
     * @param string|class-string<Listener> $identifier
     * @return Listener
     */
    public function createListener(string $identifier) : Listener {
        assert(is_a($identifier, Listener::class, true));
        return new $identifier();
    }
}
