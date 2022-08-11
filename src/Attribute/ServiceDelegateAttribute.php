<?php

namespace Cspray\AnnotatedContainer\Attribute;

interface ServiceDelegateAttribute {

    public function getService() : ?string;

}