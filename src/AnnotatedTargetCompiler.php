<?php

namespace Cspray\AnnotatedContainer;

use Generator;

interface AnnotatedTargetCompiler {

    public function compile(array $dirs, AnnotatedTargetConsumer $consumer) : void;

}