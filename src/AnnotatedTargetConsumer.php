<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface AnnotatedTargetConsumer {

    public function consume(AnnotatedTarget $annotatedTarget) : void;

}