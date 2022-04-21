<?php

namespace Cspray\AnnotatedContainer;

interface AnnotatedTargetProvider {

    /**
     * @return AnnotatedTarget[]
     */
    public function getTargets() : array;

}