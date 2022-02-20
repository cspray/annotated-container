<?php

namespace Cspray\AnnotatedContainer;

interface ServiceDelegateDefinition {

    public function getDelegateType() : string;

    public function getDelegateMethod() : string;

    public function getServiceType() : ServiceDefinition;

}