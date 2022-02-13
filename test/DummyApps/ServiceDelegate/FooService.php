<?php

namespace Cspray\AnnotatedInjector\DummyApps\ServiceDelegate;

use Cspray\AnnotatedInjector\Attribute\Service;

#[Service]
class FooService {

    public function getValue() : string {
        return 'From FooService';
    }

}