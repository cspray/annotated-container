<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis\Event;

use Cspray\AnnotatedContainer\Event\AbstractListenerProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\ServiceDefinitionDetails;

/**
 * @extends AbstractListenerProvider<ServiceDefinitionDetails>
 */
abstract class AddedServiceDefinitionListener extends AbstractListenerProvider {

    abstract protected function handle(AddedServiceDefinition $addedServiceDefinition) : void;

}