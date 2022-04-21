<?php

namespace Cspray\AnnotatedContainer;

interface AnnotatedTargetDefinitionConverter {

    public function convert(AnnotatedTarget $target) : ServiceDefinition|ServicePrepareDefinition|ServiceDelegateDefinition|InjectScalarDefinition|InjectServiceDefinition;

}