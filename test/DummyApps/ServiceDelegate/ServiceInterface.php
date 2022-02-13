<?php

namespace Cspray\AnnotatedContainer\DummyApps\ServiceDelegate;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface ServiceInterface {

    public function getValue() : string;

}