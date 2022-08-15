<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Autowire;

use Cspray\AnnotatedContainer\AutowireableParameterSet;

/**
 * Invoke a callable, autowiring any dependencies that it might have.
 */
interface AutowireableInvoker {

    /**
     *
     *
     * @param callable $callable
     * @param AutowireableParameterSet|null $parameters
     * @return mixed
     */
    public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed;

}