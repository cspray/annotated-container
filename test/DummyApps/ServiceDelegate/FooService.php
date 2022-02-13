<?php

namespace Cspray\AnnotatedContainer\DummyApps\ServiceDelegate;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooService {

    public function getValue() : string {
        return 'From FooService';
    }

}