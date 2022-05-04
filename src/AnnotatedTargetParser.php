<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Generator;

interface AnnotatedTargetParser {

    public function parse(array $dirs, AnnotatedTargetConsumer $consumer) : void;

}