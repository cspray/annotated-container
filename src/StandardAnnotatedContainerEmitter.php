<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @deprecated This class is designated to be removed in 2.0
 */
final class StandardAnnotatedContainerEmitter implements AnnotatedContainerEmitter {

    /**
     * @var list<AnnotatedContainerListener>
     */
    private array $listeners = [];

    private LoggerInterface $logger;

    public function __construct() {
        $this->logger = new NullLogger();
    }

    public function registerListener(AnnotatedContainerListener $listener) : void {
        $this->listeners[] = $listener;
        $this->logger->info(
            sprintf('Registering listener %s.', $listener::class),
            [
                'emitter' => $this::class,
                'listener' => $listener::class
            ]
        );
    }

    public function trigger(AnnotatedContainerEvent $event) : void {
        foreach ($this->listeners as $listener) {
            $this->logger->info(
                sprintf('Triggering %s listener with %s.', $listener::class, $event::class),
                [
                    'emitter' => $this::class,
                    'listener' => $listener::class,
                    'event' => $event::class
                ]
            );
            $listener->handle($event);
        }
    }

    public function setLogger(LoggerInterface $logger) : void {
        $this->logger = $logger;
    }
}