<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\Typiphy\ObjectType;

function autowiredParams(AutowireableParameter $parameter, AutowireableParameter... $additionalParameters) : AutowireableParameterList {

}

function serviceParam(string $name, ObjectType $objectType) : AutowireableParameter {

}

function param(string $name, mixed $value) : AutowireableParameter {

}