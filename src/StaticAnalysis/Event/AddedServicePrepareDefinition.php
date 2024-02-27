<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis\Event;

use Cspray\AnnotatedContainer\Event\AbstractEvent;
use Cspray\AnnotatedContainer\StaticAnalysis\ServicePrepareDefinitionDetails;

/**
 * @extends AbstractEvent<ServicePrepareDefinitionDetails>
 */
final class AddedServicePrepareDefinition extends AbstractEvent {

}