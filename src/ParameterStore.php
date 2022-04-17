<?php

namespace Cspray\AnnotatedContainer;

use Cspray\Typiphy\Type;

interface ParameterStore {

    public function getName() : string;

    public function fetch(Type $type, string $key) : mixed;

}