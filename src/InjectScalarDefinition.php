<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface InjectScalarDefinition {

    public function getService() : ServiceDefinition;

    public function getMethod() : string;

    public function getParamName() : string;

    public function getParamType() : ScalarType;

    public function getValue() : string|int|float|bool|array;

}