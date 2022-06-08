<?php

namespace Cspray\AnnotatedContainer;

use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;

interface ParameterStore {

    public function getName() : string;

    public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : mixed;

}