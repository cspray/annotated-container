<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Event\Listener;

interface ListenerFactory {

    public function createListener(string $identifier) : Listener;

}