<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis\Event;

use Cspray\AnnotatedContainer\Event\AbstractEvent;
use Cspray\AnnotatedContainer\StaticAnalysis\ServiceDefinitionDetails;

/**
 * @extends AbstractEvent<ServiceDefinitionDetails>
 */
final class AddedServiceDefinition extends AbstractEvent {

    public function __construct(ServiceDefinitionDetails $serviceDefinitionDetails) {

    }

}