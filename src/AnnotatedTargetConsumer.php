<?php

namespace Cspray\AnnotatedContainer;

interface AnnotatedTargetConsumer {

    public function consume(AnnotatedTarget $annotatedTarget) : void;

}