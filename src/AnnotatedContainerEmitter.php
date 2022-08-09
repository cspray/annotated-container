<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Psr\Log\LoggerAwareInterface;

interface AnnotatedContainerEmitter extends LoggerAwareInterface {

    public function registerListener(AnnotatedContainerListener $listener) : void;

    public function trigger(AnnotatedContainerEvent $event) : void;

}