<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface AnnotatedTargetProvider {

    /**
     * @return AnnotatedTarget[]
     */
    public function getTargets() : array;

}