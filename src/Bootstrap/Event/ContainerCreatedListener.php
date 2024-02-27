<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap\Event;

use Cspray\AnnotatedContainer\Bootstrap\ContainerCreatedDetails;
use Cspray\AnnotatedContainer\Event\AbstractListenerProvider;

/**
 * @extends AbstractListenerProvider<ContainerCreatedDetails>
 */
abstract class ContainerCreatedListener extends AbstractListenerProvider {

    abstract protected function handle(ContainerCreatedDetails $containerCreatedDetails) : void;

}
