<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface InjectServiceDefinition {

    public function getService() : ServiceDefinition;

    public function getMethod() : string;

    public function getParamName() : string;

    public function getParamType() : string;

    public function getInjectedService() : ServiceDefinition;

}