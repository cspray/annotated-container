<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface AutowireableFactory {

    public function make(string $classType, AutowireableParameterSet $parameters = null) : object;

}