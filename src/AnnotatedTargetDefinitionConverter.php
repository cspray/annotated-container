<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface AnnotatedTargetDefinitionConverter {

    public function convert(AnnotatedTarget $target) : ServiceDefinition|ServicePrepareDefinition|ServiceDelegateDefinition;

}