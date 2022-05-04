<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Countable;
use IteratorAggregate;

interface AutowireableParameterList extends Countable, IteratorAggregate {

    public function add(AutowireableParameter $autowireableParameter) : void;

    public function get(int $index) : AutowireableParameter;

    public function has(int $index) : bool;

}