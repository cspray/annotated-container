<?php

namespace Cspray\AnnotatedInjector\DummyApps\ServiceDelegate;

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
interface ServiceInterface {

    public function getValue() : string;

}