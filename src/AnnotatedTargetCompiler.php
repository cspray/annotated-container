<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Generator;

interface AnnotatedTargetCompiler {

    public function compile(array $dirs, AnnotatedTargetConsumer $consumer) : void;

}