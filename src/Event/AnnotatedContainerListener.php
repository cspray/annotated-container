<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event;

interface AnnotatedContainerListener {

    public function handle(AnnotatedContainerEvent $event) : void;

}