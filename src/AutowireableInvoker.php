<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface AutowireableInvoker {

    public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed;

}