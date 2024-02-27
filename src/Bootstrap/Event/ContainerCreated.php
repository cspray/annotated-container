<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap\Event;

use Cspray\AnnotatedContainer\Bootstrap\ContainerCreatedDetails;
use Cspray\AnnotatedContainer\Event\AbstractEvent;

/**
 * @extends AbstractEvent<ContainerCreatedDetails>
 */
class ContainerCreated extends AbstractEvent {

    public function __construct(ContainerCreatedDetails $details) {

    }

}
