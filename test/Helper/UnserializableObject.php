<?php

namespace Cspray\AnnotatedContainer\Helper;

class UnserializableObject {

    private $callable;

    public function __construct() {
        $this->callable = function() {};
    }

}