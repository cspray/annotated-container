<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis\Event;

abstract class AddedServicePrepareDefinitionListener {

    abstract protected function handle(AddedServicePrepareDefinition $addedServicePrepareDefinition) : void;

}
