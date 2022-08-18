<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Psr\Log\LoggerAwareInterface;

/**
 * @deprecated This class is designated to be removed in 2.0
 */
interface AnnotatedContainerEmitter extends LoggerAwareInterface {

    public function registerListener(AnnotatedContainerListener $listener) : void;

    public function trigger(AnnotatedContainerEvent $event) : void;

}