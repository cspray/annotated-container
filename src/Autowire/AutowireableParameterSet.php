<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Autowire;

use Countable;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameter;
use Cspray\AnnotatedContainer\Exception\InvalidParameterException;
use Cspray\AnnotatedContainer\Exception\ParameterNotFoundException;
use IteratorAggregate;

/**
 * A set of AutowireableParameters that can be used with the AutowireableFactory and AutowireableInvoker.
 *
 * @see autowiredParams()
 */
interface AutowireableParameterSet extends Countable, IteratorAggregate {

    /**
     * @param AutowireableParameter $autowireableParameter The parameter that should be added to the set
     * @throws InvalidParameterException Thrown if there is already an added parameter with the same name as the
     *                                   $autowireableParameter
     * @return void
     */
    public function add(AutowireableParameter $autowireableParameter) : void;

    /**
     * @param int $index The 0-based index for which parameter to retrieve
     * @throws ParameterNotFoundException Thrown if an $index is passed that has no corresponding parameter
     * @return AutowireableParameter The parameter at the given index.
     */
    public function get(int $index) : AutowireableParameter;

    /**
     * @param int $index The 0-based index for which parameter to check the existence of
     * @return bool Whether a parameter can be found at the given index.
     */
    public function has(int $index) : bool;

}