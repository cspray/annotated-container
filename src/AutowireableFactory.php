<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface AutowireableFactory {

    public function make(string $classType, AutowireableParameterList $parameters = null) : object;

}